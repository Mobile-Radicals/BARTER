<?php
require_once("../config/linkedin_config.php");

function refreshToken()
{
	$endpoint = "https://api.linkedin.com/uas/oauth/requestToken";
	$key = API_KEY;
	$secret = API_SECRET;
	$params = array( 
		"oauth_version" => "1.0", 
		"oauth_nonce" => time(), 
		"oauth_timestamp" => time(), 
		"oauth_consumer_key" => $key, 
		"oauth_signature_method" => "HMAC-SHA1" 
	);
	
	$baseString = "GET&" . urlencode($endpoint) . "&" . urlencode(SortedArgumentString($params));
	$params['oauth_signature'] = urlencode(base64_encode(hash_hmac('sha1', $baseString, $secret."&", TRUE))); 
	//echo "<a href=\"" . $endpoint . "?" . SortedArgumentString($params) . "\">Get Token<a/><br/>"; 
	$url = $endpoint . "?" . SortedArgumentString($params);
	
	 
				
	$response = file_get_contents($url, false);
	
	parse_str($response, $output);
	 
	//echo ($output['oauth_token']);
	
	$url1 = "https//www.linkedin.com/uas/oauth/authenticate?oauth_token=".$output['oauth_token'];
	
	echo $url1;
//	$response1 = file_get_contents($url1, false);
	
	//echo $response1;
	//return $response1;
}

function SortedArgumentString($inKV)
{
	uksort($inKV, 'strcmp');
	foreach ($inKV as $k => $v)
		$argument[] = $k."=".$v;
	return implode('&', $argument); 
}
?>