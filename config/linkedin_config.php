<?php
// Change these
define('API_KEY','3ouaadgqwv3a');
define('API_SECRET','H4ZwbqR11Xwf5vrp');
define('REDIRECT_URI','http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
define('SCOPE','r_fullprofile r_emailaddress rw_nus r_network w_messages');

function fetch($method, $resource, $body = '', $token) {
    $params = array('oauth2_access_token' => $token,
                    'format' => 'json',
              );
     
    // Need to use HTTPS
    $url = $resource . '?' . http_build_query($params);

    // Tell streams to make a (GET, POST, PUT, or DELETE) request
    $context = stream_context_create(
                    array('http' => 
                        array('method' => $method,
                       )
                    )
                );
 
 
    // Hocus Pocus
    $response = file_get_contents($url, false, $context);
 	//$response = file_get_contents($url);
 	
	//echo $response;
	// Native PHP objct, please
    $data = json_decode($response);
	return $data;
	
	//var_dump($data);
}


?>