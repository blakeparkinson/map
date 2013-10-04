<?php
/**
 * Copyright 2013, Edmodo, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this work except in compliance with the License.
 * You may obtain a copy of the License in the LICENSE file, or at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS"
 * BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 * Created by JetBrains PhpStorm.
 * User: doug
 * Date: 5/31/13
 * Time: 10:49 AM
 * To change this template use File | Settings | File Templates.
 */

include_once $root . '/skeleton_dist/edmodo_client.php';

/**
 * Various keys for encryption: make your own.
 */
define(COOKIE_SECRET_KEY, 'Replace-this-string');
define(EDMODO_ID_SECRET_KEY, 'Replace-this-string');

/**
 * Which locale are we using?
 * (FIXME)
 * Stubbed out.
 */
function getBrowserLocale() {
  return 'en-us';
}

/**
 * @param $value
 * @return bool|string
 * Encrypt cookie using our key.
 */
function encryptCookie($value) {
  return encryptString($value, COOKIE_SECRET_KEY);
}

/**
 * @param $value
 * @return bool|string
 * Decrypt cookie using our key.
 */
function decryptCookie($value) {
  return decryptString($value, COOKIE_SECRET_KEY);
}

/**
 * @param $value
 * @return bool|string
 *
 * Encrypt an edmodo id (user token, group id, etc.
 * Note: $value must be a string!
 */
function encryptEdmodoId($value) {
  return encryptString($value, EDMODO_ID_SECRET_KEY);
}

/**
 * @param $value
 * @return bool|string
 *
 * Decrupt an edmodo_id.
 */
function decryptEdmodoId($value) {
  return decryptString($value, EDMODO_ID_SECRET_KEY);
}

/**
 * @param $value
 * @param $secretKey
 * @return bool|string
 * Generic encryption function.
 */
function encryptString($value, $secretKey) {
   if(!$value){return false;}
   $text = $value;
   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
   $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $secretKey,
     $text, MCRYPT_MODE_ECB, $iv);
   return trim(base64_encode($crypttext)); //encode for cookie
}

/**
 * @param $value
 * @param $secretKey
 * @return bool|string
 * Generic decryption function.
 */
function decryptString($value, $secretKey) {
   if(!$value){return false;}
   $crypttext = base64_decode($value); //decode cookie
   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
   $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $secretKey,
     $crypttext, MCRYPT_MODE_ECB, $iv);
   return trim($decrypttext);
}


/**
 * @param $httpCode
 *
 * Set http response code to some error value.
 */
function setHTTPReponseCode($httpCode) {
  header('HTTP/1.1 ' . $httpCode, true, $httpCode);
}

/**
 * @param $httpCode
 * @param $message
 *
 * Some json request failed, hand back code/message details as
 * json.
 */
function errorJson($httpCode, $message) {
  setHTTPReponseCode($httpCode);
  header('Content-type: application/json');
  echo $message;
};

/**
 * @param $httpCode
 * @param $oResults
 *
 * Some json request failed b/c conversation with edmodo
 * servers failed.  Hand back code/details as json.
 */
function errorJsonBadEdmodoResults($httpCode, $oResults) {
  $error = $oResults->response['error'];
  errorJson($httpCode, print_r($error, True));
};

/**
 * @param $httpCode
 * @param $message
 *
 * Some request for html failed, hand back code/message details as
 * html page.
 */
function errorPage($httpCode, $message) {
  setHTTPReponseCode($httpCode);
  echo 'HTTP Error Code: ' .$httpCode . '<br>';
  echo 'Details from Edmodo API: ' . $message;
};

/**
 * @param $httpCode
 * @param $resultsObject
 *
 * Some json request failed b/c conversation with edmodo
 * servers failed.  Hand back code/details as html page.
 */
function errorPageBadEdmodoResults($httpCode, $oResults) {
  $error = $oResults->response['error'];
  errorPage($httpCode, print_r($error, True));
}


/**
 * @param $aGroup
 *
 * Hide edmodo keys and object ids so client can't communicate directly
 * with edmodo servers.
 */
function sanitizeGroup($aGroup) {
  $aGroup['group_id'] = encryptEdmodoId((string)($aGroup['group_id']));
  if ($aGroup['owners']) {
    $aGroup['owners'] = array_map('encryptEdmodoId', $aGroup['owners']);
  }
  if ($aGroup['owner_ids']) {
    $aGroup['owner_ids'] = array_map('encryptEdmodoId', $aGroup['owner_ids']);
  }
  return $aGroup;
}

/**
 * @param $aUser
 *
 * Hide edmodo keys and object ids so client can't communicate directly
 * with edmodo servers.
 */
function sanitizeUser($aUser) {
  $aUser['user_id'] = encryptEdmodoId($aUser['user_id']);
  $aUser['user_token'] = encryptEdmodoId($aUser['user_token']);

  // Just clean out group info, we get that from calling groups for user.
  if ($aUser['groups']) {
    unset($aUser['groups']);
  }
  if ($aUser['district_id']) {
    $aUser['district_id'] = encryptEdmodoId($aUser['district_id']);
  }
  if ($aUser['school_id']) {
    $aUser['school_id'] = encryptEdmodoId($aUser['school_id']);
  }
  return $aUser;
}


/**
 * @param $name
 * @param $defaultValue
 * @return mixed
 * Get the value of given cgi arg.  If not present use default.
 */
function getCgiArg($name, $defaultValue) {
  $value = $_REQUEST[$name];
  if ($value == null) {
    return $defaultValue;
  } else {
    return $value;
  }
};

function getIntCgiArg($name, $defaultValue) {
  $value = $_REQUEST[$name];
  if ($value == null) {
    return $defaultValue;
  } else {
    return intval($value, 10);
  }
}

/**
 * @param $name
 * @param $defaultValues
 * @return array
 * get value of given cgi arg.  Parse as csv and return array of values.
 * If not present return default values.
 */
function getCSVCgiArg($name, $defaultValues) {
  $values = $_REQUEST[$name];
  if ($values == null) {
    return $defaultValues;
  } else {
    return json_decode($values);
  }
};

function getDictionaryCgiArg($name, $defaultValue) {
  $values = $_REQUEST[$name];
  if ($values == null) {
    return $defaultValue;
  } else {
    return json_decode($values);
  }
};


/**
 * FIXME
 * Override with whatever configuration scheme you like.
 * For now, we just default to using the sandbox api.
 */
function getAppConfig() {
  $REQUEST_CONFIGS = array();

  $REQUEST_CONFIGS['apiEndpoint'] = "https://appsapi.edmodobox.com/";
  // FIXME(developer)
  // Replace with the app key you get from creating your app on the sandbox.
  $REQUEST_CONFIGS['apiKey'] = "{YOUR API KEY HERE!}";
  $REQUEST_CONFIGS['cookieName'] = 'sandboxApp';
  $REQUEST_CONFIGS['debug'] = 'true';
  return $REQUEST_CONFIGS;
};

function getCookieName() {
  $config = getAppConfig();
  return $config['cookieName'];
}

/**
 * @param $edmodoClient
 * @return null
 *
 * Fetch the edmodo user and set an encrypted cookie based on his user token.
 *
 * This includes a  bit of hackery for fetching the user on launch:
 *
 * When debugging, it gets tiresome to launch the app from edmodo sandbox.
 *
 * If you can get a userToken (breakpoints, print statements, whatever), you can
 * go directly to
 * <your launch url>&hack_user_token=<userToken>
 * and your app will run as it would inside the edmodo app iframe.
 */
function getActiveUserOnLaunch($edmodoClient) {
  // If we are in debug mode, look for the hack user token.
  $config = getAppConfig();
  $hackUserToken = getCgiArg('hack_user_token', null);

  if ($hackUserToken && $config['debug']) {
    $oResults = $edmodoClient->getUsers(array($hackUserToken));
    if ($oResults->ok) {
      if ($oResults->response && count($oResults->response) == 1) {
        return $oResults->response[0];
      } else {
        errorPage(400, "API didn't fail but found no user with given hack_user_token.");
        return;
      }
    } else {
      errorJsonBadEdmodoResults(400, $oResults);
      return;
    }
  } else {
    $launchKey = $_GET['launch_key'];
    if (!$launchKey) {
      errorPage(400, "Missing Launch Key");
      return null;
    } else {
      // Use launch key to get edmodo user data and user token.
      $oLaunchResults = $edmodoClient->getLaunchRequest($launchKey);
      if (!$oLaunchResults->ok ) {
        // bad launch key.
        errorPageBadEdmodoResults(400, $oLaunchResults);
        return null;
      }

      return $oLaunchResults->response;
    }
  }
};

/**
 * @return EdmodoCustomClient
 * Make the edmodo custom client object.
 */
function makeEdmodoClient() {
  $config = getAppConfig();
  $options = array(
    'api_key' => $config['apiKey'],
    'endpoint' => $config['apiEndpoint'],
    'response_type' => EdmodoAppsApiClient::RESPONSE_TYPE_ASSOC,
    'response_format' => EdmodoAppsApiClient::JSON,
    'client_timeout' => 5,
  );
  return new EdmodoAppsApiClient($options);
};

/**
 * @param $userToken
 * Encode and set cookie
 */
function setCookieWithUserToken($userToken) {
  // FIXME(dbanks)
  // Deal with session length later.
  setcookie(getCookieName(), encryptCookie($userToken), time() + 24 * 60 * 60, '/');
}

/**
 * @param $userToken
 * Decode cookie.
 */
function getAuthenticatedUserToken() {
  $cookie = $_COOKIE[getCookieName()];
  if (!$cookie) {
    return null;
  } else {
    return decryptCookie($cookie);
  }
}
