<?php

function query_user($uid)
{
	//get the consumer_rfid from the databas
	$stmt = DB::get()->prepare("SELECT  linkedin_token,linkedin_expires_in,linkedin_expires_at, linkedin_id,linkedin_headline,linkedin_industry,linkedin_img,linkedin_summary FROM tbl_users WHERE user_id=:uid");
	$stmt->bindParam(':uid', $uid, PDO::PARAM_STR);
	$stmt->execute();
		
	//setting the fetch mode  
	$stmt->setFetchMode(PDO::FETCH_ASSOC); 
	$row = $stmt->rowCount();

	$stmt->bindColumn('linkedin_token', $linkedin_token);
	$stmt->bindColumn('linkedin_id', $linkedin_id);
	$stmt->bindColumn('linkedin_expires_in', $linkedin_expires_in);
	$stmt->bindColumn('linkedin_expires_at', $linkedin_expires_at);
	$stmt->bindColumn('linkedin_headline', $linkedin_headline);
	$stmt->bindColumn('linkedin_industry', $linkedin_industry);
	$stmt->bindColumn('linkedin_img', $linkedin_img);
	$stmt->bindColumn('linkedin_summary', $linkedin_summary);
	$stmt->fetch(PDO::FETCH_BOUND);
	
	//found the user	
	$ajax_response = false;
		
	$data = array();	
	if($row == 1)
	{ 
		array_push($data, $linkedin_token, $linkedin_id,$linkedin_expires_in, $linkedin_expires_at, $linkedin_headline,$linkedin_industry,$linkedin_img,$linkedin_summary);
		
	}
	else
	{
	}
	return $data;
}


function fetch_linkedin_connections($token)
{
	//$obj = fetch('GET', 'https://api.linkedin.com/v1/people/~:(id,headline,industry,picture-urls::(original),summary)','',$_SESSION['access_token']);
	$obj = fetch('GET', 'https://api.linkedin.com/v1/people/~/connections:(id,headline,first-name,last-name)','',$token);
	//var_dump($obj);
	return $obj;
}
?>