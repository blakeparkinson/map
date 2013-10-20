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
 

$root = ($_SERVER["DOCUMENT_ROOT"]);

include_once $root . '/blake/map/lib/edmodo_client.php';
//define(MAIN_PREFIX, $root . '/blake/map');
//define($assets, MAIN_PREFIX . "/assets");


$assets = '../../assets';
echo $blah;


$scripts = array(
  $assets . '/js/bootstrap.js',
  $assets . '/js/bootstap.min.js',
  $assets . '/js/jquery-2.0.3.js',
  $assets . '/js/models.js',
  $assets . '/js/views.js',
  $assets . '/js/controllers.js',
);

$stylesheets = array(
	$assets . '/css/bootstrap-theme.css',
	$assets . '/css/bootstrap-theme.min.css',
	$assets . '/css/bootstrap.css',
	$assets . '/css/bootstrap.min.css',
);

$fonts = array(
	$assets . 'glyphicons-halflings-regular.eot',
	$assets . 'glyphicons-halflings-regular.svg',
	$assets . 'glyphicons-halflings-regular.ttf',
	$assets . 'glyphicons-halflings-regular.woff',
);


$options = array(
  'api_key' => "4eeaf84b9673f145a470cdb5f30393957b064bb7",
  'endpoint' => 'https://appsapi.edmodobox.com/v1.1/',
  'response_type' => EdmodoAppsApiClient::RESPONSE_TYPE_ASSOC,
  'response_format' => EdmodoAppsApiClient::JSON,
);

$edmodoClient = new EdmodoAppsApiClient($options);


// Keep in mind this is a php array: not a class, not a string.
$results = $edmodoClient->getLaunchRequest($_REQUEST['launch_key']);
$response = $results->response;

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8>"
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="../../assets/css/bootstrap.css" rel="stylesheet">
    <link href="../../assets/css/bootstrap-responsive.min.css" rel="stylesheet">

<style>
    body {
    padding-top: 60px; /* When using the navbar-top-fixed */
    }
</style>
<link href="../../assets/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="../../assets/js/jqvmap/jqvmap.css" media="screen" rel="stylesheet" type="text/css" />


  </head>
  <body>
  	<header class="navbar navbar-inverse navbar-fixed-top bs-docs-nav" role="banner">
  <div class="container">
    <div class="navbar-header">
      <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="../" class="navbar-brand">Edmodo Pen Pals</a>
    </div>
    <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
      <ul class="nav navbar-nav">
        <li>
          <a href="../getting-started">My Pals</a>
        </li>
      </ul>
    </nav>
  </div>
</header>
<h3>Select a country to find a penpal!</h3>

      <div id="vmap" style="width: 1400px; height: 600px;"></div> 

 </body>
</html>
			<script src="http://code.jquery.com/jquery.js"></script>
<script src="../../assets/js/jqvmap/jquery.vmap.js" type="text/javascript"></script>
    <script src="../../assets/js/jqvmap/maps/jquery.vmap.world.js" type="text/javascript"></script>
    <script src="../../assets/js/bootstrap.min.js"></script>
    <script type="text/javascript">
    </script>

  <script type="text/javascript">
    jQuery('#vmap').vectorMap(
{
    map: 'world_en',
    backgroundColor: null,
    borderColor: '#818181',
    borderOpacity: 0.25,
    borderWidth: 1,
    color: '#ffffff',
    enableZoom: true,
    hoverColor: '#c9dfaf',
    hoverOpacity: null,
    normalizeFunction: 'polynomial',
	scaleColors: ['#C8EEFF', '#006491'],
    selectedColor: '#c9dfaf',
    selectedRegion: null,
    showTooltip: true,
    onRegionClick: function(event, code, region)
    {
        switch (code) {
        case "us":
            window.location.replace("../../views/map/usa.html");
            break;
        case "ca":
            window.location.replace("http://www.yahoo.com");
            break;
        case "tx":
            window.location.replace("http://www.bing.com");
            break;
            }

    }
});
    </script>



