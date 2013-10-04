<?php
/**
 * Edmodo Apps API PHP Client
 *
 * Revised: 2013-09-15
 *
 * @version beta v1.1s  (default sandbox endpoint)
 * @author apps@edmodo.com
 *
 * Edmodo Apps Platform
 * http://www.edmodo.com/platform
 */

class EdmodoAppsApiClient
{
  protected static $APPS_API_ENDPOINT = 'https://appsapi.blake-west.edmotrunk.com/v1/';

  private $_options = array(
    'api_key' => null, // API Key
    'endpoint' => null, // Apps API endpoint
    'response_format' => null, // json or xml
    'response_type' => null, // 'assoc' for an associative array or 'object' for std object
    'client_timeout' => null, // number of seconds to wait for the client to complete it's request
    'log_path' => null, // specify the path to the location where a log of API requests should be logged to (ex: /var/log/ or /var/log/edmodo_apps_api.log)
  );

  private $_atok = null; // access token
  private $_log_path = null;

  const HTTP_POST = 'POST';
  const HTTP_GET = 'GET';
  const JSON = 'json';
  const XML = 'xml';
  const RESPONSE_TYPE_OBJECT = 'object'; // response type object
  const RESPONSE_TYPE_ASSOC = 'assoc'; // response type associative array
  const USER_TYPE_TEACHER = 'TEACHER';
  const USER_TYPE_STUDENT = 'STUDENT';
  const DEFAULT_CLIENT_TIMEOUT = 10; // default client timeout in seconds
  const APPS_API_LOG_FILENAME = 'edmodo_apps_api.log';

  /**
   * Constructor
   * @param array $options, array of options to set
   */
  public function __construct($options) {
    // set defaults
    $this->_options['endpoint'] = self::$APPS_API_ENDPOINT;
    $this->_options['response_format'] = self::JSON;
    $this->_options['response_type'] = self::RESPONSE_TYPE_OBJECT;
    $this->_options['client_timeout'] = self::DEFAULT_CLIENT_TIMEOUT;

    // set options (if any)
    $this->setOptions($options);
  }

  /**
   * True iff this endpoint ends in '.com/vX/' for X = some decimal number.
   * @param $endpoint
   */
  private function endsWithVersion($endpoint) {
    return preg_match('/.*\.com\/v[0-9]+(\.[0-9]+)?\/$/', $endpoint);
  }

  /**
   * Does this instance of the client use access tokens?
   * Yes, unless version is v1.
   */
  public function requiresAccessToken() {
    $pieces = explode("/", $this->_options['endpoint']);
    $end = end($pieces);
    return ($end == 'v1');
  }

  /**
   * Set client options into $_options array
   * @param array $options, array of options to set
   */
  public function setOptions($options = array()) {
    // set options
    if (is_array($options) && $options) {
      foreach (array_keys($this->_options) as $option_name) {
        if (isset($options[$option_name])) {
          $option_value = $options[$option_name];

          // options processing
          switch ($option_name) {
            case 'endpoint':
              // Make sure it includes a version, default to v1.
              if (!$this->endsWithVersion($option_value)) {
                $option_value = $option_value . 'v1/';
              }
              break;
            case 'log_path':
              $option_value = $this->_getLogPath($option_value);

              // set the option only if the file is writable
              if (!is_writable($option_value)) {
                // log_path is not writable
                continue;
              }

              break;

            default:
              break;
          }

          $this->_options[$option_name] = $option_value;
        }
      }

      return true;
    } else {
      return false;
    }
  }

  /**
   * Gets the full path to the log file given a specified file path.
   * @param string $log_path
   * @return string
   */
  protected function _getLogPath($log_path) {
    // check to see if the path specified is a directory or a file
    if (is_dir($log_path)) {
      // append trailing '/' to log path if one was not specified
      if (!substr($log_path, -1) === '/') {
        $log_path .= '/';
      }

      $log_path .= self::APPS_API_LOG_FILENAME;
    }

    return $log_path;
  }

  /**
   * Get client option by name
   */
  public function getOption($option_name) {
    if (isset($this->_options[$option_name])) {
      return $this->_options[$option_name];
    } else {
      return null;
    }
  }

  /**
   * Set API access token to be used by the client
   * @param string $access_token
   */
  public function setAccessToken($access_token) {
    $this->_atok = $access_token;
  }

  /**
   * Verify a launch request
   * @param string $launch_key
   */
  public function getLaunchRequest($launch_key) {
    $resource = 'launchRequests';
    $method = self::HTTP_GET;

    $payload = array(
      'launch_key' => $launch_key,
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /users
   * @param array $user_tokens
   */
  public function getUsers($user_tokens) {
    if (!is_array($user_tokens)) {
      $user_tokens = array($user_tokens);
    }

    $resource = 'users';
    $method = self::HTTP_GET;

    $payload = array(
      'user_tokens' => json_encode($user_tokens),
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /groups
   * @param array $group_ids
   */
  public function getGroups($group_ids) {
    $resource = 'groups';
    $method = self::HTTP_GET;

    $payload = array(
      'group_ids' => json_encode($group_ids)
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /groupsForUser
   * @param string $user_tokens
   */
  public function getGroupsForUser($user_token) {
    $resource = 'groupsForUser';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /members
   * @param int $group_id
   */
  public function getMembers($group_id) {
    $resource = 'members';
    $method = self::HTTP_GET;

    $payload = array(
      'group_id' => $group_id
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /classmates
   * @param string $user_token
   */
  public function getClassmates($user_token) {
    $resource = 'classmates';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /teachers
   * @param string $user_token
   */
  public function getTeachers($user_token) {
    $resource = 'teachers';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /teachermates
   * @param string $user_token
   */
  public function getTeachermates($user_token) {
    $resource = 'teachermates';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /teacherConnections
   * @param string $user_token
   */
  public function getTeacherConnections($user_token) {
    $resource = 'teacherConnections';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /assignmentsComingDue
   * @param string $user_token
   */
  public function getAssignmentsComingDue($user_token) {
    $resource = 'assignmentsComingDue';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /gradesSetByAppForUser
   * @param string $user_token
   */
  public function getGradesSetByAppForUser($user_token) {
    $resource = 'gradesSetByAppForUser';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /gradesSetByAppForGroup
   * @param int $group_id
   */
  public function getGradesSetByAppForGroup($group_id) {
    $resource = 'gradesSetByAppForGroup';
    $method = self::HTTP_GET;

    $payload = array(
      'group_id' => $group_id
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * POST /newGrade
   * @param int $group_id
   * @param string $title
   * @param int $total
   */
  public function createNewGrade($group_id, $title, $total) {
    $resource = 'newGrade';
    $method = self::HTTP_POST;

    $payload = array(
      'group_id' => $group_id,
      'title' => $title,
      'total' => $total,
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /badgesAwarded
   * @param string $user_token
   */
  public function getBadgesAwarded($user_token) {
    $resource = 'badgesAwarded';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token,
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * POST /registerBadge
   * @param string $badge_title
   * @param string $description
   * @param string $image_url
   */
  public function registerBadge($badge_title, $description, $image_url) {
    $resource = 'registerBadge';
    $method = self::HTTP_POST;

    $payload = array(
      'badge_title' => $badge_title,
      'description' => $description,
      'image_url' => $image_url,
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * POST /awardBadge
   * @param int $badge_id
   * @param string $user_token
   */
  public function awardBadge($badge_id, $user_token) {
    $resource = 'awardBadge';
    $method = self::HTTP_POST;

    $payload = array(
      'badge_id' => $badge_id,
      'user_token' => $user_token,
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * POST /revokeBadge
   * @param int $badge_id
   * @param string $user_token
   */
  public function revokeBadge($badge_id, $user_token) {
    $resource = 'revokeBadge';
    $method = self::HTTP_POST;

    $payload = array(
      'badge_id' => $badge_id,
      'user_token' => $user_token,
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * POST /updateBadge
   * @param int $badge_id
   * @param string $badge_title
   * @param string $description
   * @param string $image_url
   */
  public function updateBadge($badge_id, $badge_title, $description, $image_url) {
    $resource = 'updateBadge';
    $method = self::HTTP_POST;

    $payload = array(
      'badge_id' => $badge_id,
      'badge_title' => $badge_title,
      'description' => $description,
      'image_url' => $image_url,
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /eventsByApp
   * @param string $user_token
   */
  public function getEventsByApp($user_token) {
    $resource = 'eventsByApp';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token,
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * POST /newEvent
   * @param string $user_token
   * @param string $description, the description of the event
   * @param string $start_date, start date in format YYYY-MM-DD
   * @param string $end_date, end_date in format YYYY-MM-DD
   * @param array $recipients, an array of recipients to send the event to
   *      user recipients should be identified by a user_token
   *      group recipients should be identified by a group_id
   */
  public function sendNewEvent($user_token, $description, $start_date, $end_date, $recipients) {
    $resource = 'newEvent';
    $method = self::HTTP_POST;

    $payload = array(
      'user_token' => $user_token,
      'description' => $description,
      'start_date' => $start_date,
      'end_date' => $end_date,
      'recipients' => json_encode($recipients),
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /parents
   * @param string $user_token, the student's user token
   */
  public function getParents($user_token) {
    $resource = 'parents';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token,
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /children
   * @param string $user_token, the parent's user token
   */
  public function getChildren($user_token) {
    $resource = 'children';
    $method = self::HTTP_GET;

    $payload = array(
      'user_token' => $user_token,
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /getAppData
   * @param array $keys
   */
  public function getAppData($keys) {
    $resource = 'getAppData';
    $method = self::HTTP_GET;

    $payload = array(
      'keys' => json_encode($keys)
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * POST /setAppData
   * @param array $data_array
   */
  public function setAppData($data_array) {
    $resource = 'setAppData';
    $method = self::HTTP_POST;

    $payload = array(
      'dataobject' => json_encode($data_array)
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * GET /profiles
   * @param array $user_tokens
   */
  public function getProfiles($user_tokens) {
    $resource = 'profiles';
    $method = self::HTTP_GET;

    $payload = array(
      'user_tokens' => json_encode($user_tokens),
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * POST /userPost
   * @param string $user_token
   * @param string $content, the content of the post
   * @param array $recipients, an array of recipients to send the post to
   *      user recipients should be identified by a user_token
   *      group recipients should be identified by a group_id
   * @param array $attachments, an optional array of attachments to send with the post
   */
  public function postUserPost($user_token, $content, $recipients, $attachments = array()) {
    $resource = 'userPost';
    $method = self::HTTP_POST;

    $payload = array(
      'user_token' => $user_token,
      'content' => $content,
      'recipients' => json_encode($recipients),
    );

    if ($attachments) {
      $payload['attachments'] = json_encode($attachments);
    }

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * POST /turnInAssignment
   * @param string $user_token
   * @param int $assignment_id
   * @param string $content, the content of the assignment submission
   * @param array $attachments, an optional array of attachments to send with this assignment submission
   */
  public function turnInAssignment($user_token, $assignment_id, $content, $attachments = array()) {
    $resource = 'turnInAssignment';
    $method = self::HTTP_POST;

    $payload = array(
      'user_token' => $user_token,
      'assignment_id' => $assignment_id,
      'content' => $content,
    );

    if ($attachments) {
      $payload['attachments'] = json_encode($attachments);
    }

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * POST /setNotification
   * @param string $user_token
   * @param int $notification_count
   */
  public function setNotification($user_token, $notification_count = 1) {
    $resource = 'setNotification';
    $method = self::HTTP_POST;

    $payload = array(
      'user_token' => $user_token,
      'notification_count' => $notification_count
    );

    return $this->_makeAPIRequest($resource, $method, $payload);
  }

  /**
   * Send off the API request
   * @param string $resource, the API resource
   * @param string $method, HTTP request method
   * @param array $payload, key/value array for the reqeust payload
   */
  private function _makeAPIRequest($resource, $method, $payload) {
    // make sure the endpoint option is set
    if (!isset($this->_options['endpoint']) || trim($this->_options['endpoint']) == '') {
      throw new Exception('Apps API endpoint not set');
    }

    $endpoint = $this->_options['endpoint'] . $resource . '.' . $this->_options['response_format'];

    // add api key
    if (!isset($this->_options['api_key']) ||
        trim($this->_options['api_key']) == '') {
      throw new Exception('API Key not set');
    }

    if (isset($this->_atok)) {
      $payload['access_token'] = $this->_atok;
    }

    $payload['api_key'] = $this->_options['api_key'];

    // create a new cURL resource
    $ch = curl_init();

    if ($method == self::HTTP_POST) {
      curl_setopt($ch, CURLOPT_POST, true);

      if ($payload) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
      }
    } else if ($method == self::HTTP_GET) {
      $query_string = http_build_query($payload);

      if (strpos($endpoint, '?') !== false) {
        $endpoint .= '&' . $query_string;
      } else {
        $endpoint .= '?' . $query_string;
      }
    } else {
      throw new Exception('Unsupported HTTP method [' . $method . ']');
    }

    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->_options['client_timeout']);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->_options['client_timeout']);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $raw_response = curl_exec($ch);
    $curl_response_info = curl_getinfo($ch);

    // close cURL resource, and free up system resources
    curl_close($ch);

    if (isset($curl_response_info['http_code']) && $curl_response_info['http_code'] == 200) {
      $ok = true;
    } else {
      $ok = false;
    }

    $request_response = new stdClass();
    $request_response->ok = $ok;
    $request_response->info = $curl_response_info;

    if ($this->_options['response_format'] == self::JSON) {
      $assoc = false;

      if ($this->_options['response_type'] == self::RESPONSE_TYPE_ASSOC) {
        $assoc = true;
      }

      $decoded_response = json_decode($raw_response, $assoc);
    } else if ($this->_options['response_format'] == self::XML) {
      $decoded_response = simplexml_load_string($raw_response);

      // Convert SimpleXMLElements to arry/object (Reference: http://php.net/manual/en/function.simplexml-load-string.php)

      if ($this->_options['response_type'] == self::RESPONSE_TYPE_ASSOC) {
        $decoded_response = json_decode(json_encode($decoded_response), 1);
      } else {
        $decoded_response = json_decode(json_encode($decoded_response));

        // repackage the elements collection
        $response_collection = array();

        foreach ($decoded_response->element as $element) {
          $response_collection[] = $element;
        }

        $decoded_response = $response_collection;
      }

    }

    $request_response->raw_response = $raw_response;
    $request_response->response = $decoded_response;

    // if a log path was specified, write to the log file
    if (isset($this->_options['log_path'])) {
      $log_line = array(
        'request' => $endpoint,
        'response_http_code' => $curl_response_info['http_code'],
        'response_body' => $raw_response
      );
      @file_put_contents($this->_options['log_path'], print_r($log_line, true), FILE_APPEND);
    }

    // set access token into the client
    $access_token = null;
    if ($this->_options['response_type'] == self::RESPONSE_TYPE_ASSOC) {
      $access_token = $decoded_response['access_token'];
    } else {
      $access_token = $decoded_response->access_token;
    }
    if (isset($access_token)) {
      $this->setAccessToken($access_token);
    }

    return $request_response;
  }

}