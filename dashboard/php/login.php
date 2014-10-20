<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//include the database class
require '../../config/con.php';
require '../../config/utils.php';

session_name('barter');
session_start();

//get the data
$email = mysql_escape_string($_REQUEST['email']);
$password = mysql_escape_string($_REQUEST['password']);
$password = salt_password($password);
$password = salt_password($password);

//get the consumer_rfid from the databas
$stmt = DB::get()->prepare("SELECT user_id, user_card_id, user_name, business_name, user_business_lat, user_business_lon, is_trader, logged_in FROM tbl_users WHERE user_email=:e AND user_pass=:p AND user_account_status = '1'");
$stmt->bindParam(':e', $email, PDO::PARAM_STR);
$stmt->bindParam(':p', $password, PDO::PARAM_STR);
$stmt->execute();
	
//setting the fetch mode  
$stmt->setFetchMode(PDO::FETCH_ASSOC); 
$row = $stmt->rowCount();


$stmt->bindColumn('user_id', $user_id);
$stmt->bindColumn('user_card_id', $user_card_id);
$stmt->bindColumn('user_name', $user_name);
$stmt->bindColumn('business_name', $business_name);
$stmt->bindColumn('user_business_lat', $lat);
$stmt->bindColumn('user_business_lon', $lon);
$stmt->bindColumn('is_trader', $is_trader);
$stmt->bindColumn('logged_in', $last_login);
$stmt->fetch(PDO::FETCH_BOUND);

//found the user	
$ajax_response = false;
	
if($row != 0)
{ 
	$date = new DateTime();
	$date->getTimestamp();
	$login = $date->format('Y-m-d H:i:s');
	
	$statement = DB::get()->prepare("UPDATE tbl_users SET logged_in=:li, last_login=:l WHERE user_id=:id AND user_pass=:p");
	$statement->bindParam(':p', $password, PDO::PARAM_STR);
	$statement->bindParam(':id', $user_id, PDO::PARAM_STR);
	$statement->bindParam(':l', $last_login, PDO::PARAM_STR);
	$statement->bindParam(':li', $login, PDO::PARAM_STR);
	$statement->execute();
	
	$_SESSION['uid'] = $user_id;
	$_SESSION['cid'] = $user_card_id;
	$_SESSION['lat'] = $lat;
	$_SESSION['lon'] = $lon;
	$_SESSION['e'] = $email;
	$_SESSION['p'] = $password;
	$_SESSION['n'] = $user_name;
	$_SESSION['bname'] = $business_name;
	$_SESSION['is_trader'] = $is_trader;
	
	$rows = $statement->rowCount();
	
	if($rows == 1){
		$ajax_response = true;
		
		if ($row > 1)
		{
			$no_accounts = "multiple-accounts";
		}
		else
		{
			$no_accounts = "single-accounts";
		}
		
		$ajax_message = $no_accounts;
	}
	else{
		$ajax_message = "Unfortunately, there seems to be an issue we our system at the moment, please try again later";
	}
}
else {
	$ajax_message = "Could not find the user". $row;
}

$data_to_send = array('response' => $ajax_response, 'message' => $ajax_message);
echo json_encode($data_to_send);
?>