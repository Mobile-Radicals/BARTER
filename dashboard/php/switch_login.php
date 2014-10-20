<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//include the database class
require '../../config/con.php';
require '../../config/utils.php';


session_unset();


session_name('barter');
session_start();


//get the data
$email = mysql_escape_string($_SESSION['e']);
$password = mysql_escape_string($_SESSION['p']);
$user_id = $_POST['uid'];

//get the consumer_rfid from the databas
$stmt = DB::get()->prepare("SELECT user_id, user_name, is_trader, logged_in FROM tbl_users WHERE user_id=:uid AND user_email=:e AND user_pass=:p LIMIT 1");
$stmt->bindParam(':uid', $user_id, PDO::PARAM_STR);
$stmt->bindParam(':e', $email, PDO::PARAM_STR);
$stmt->bindParam(':p', $password, PDO::PARAM_STR);
$stmt->execute();
	
//setting the fetch mode  
$stmt->setFetchMode(PDO::FETCH_ASSOC); 
$row = $stmt->rowCount();


$stmt->bindColumn('user_id', $user_id);
$stmt->bindColumn('user_name', $user_name);
$stmt->bindColumn('is_trader', $is_trader);
$stmt->bindColumn('logged_in', $last_login);
$stmt->fetch(PDO::FETCH_BOUND);

//found the user	
$ajax_response = false;
	
if($row == 1)
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
	$_SESSION['e'] = $email;
	$_SESSION['p'] = $password;
	$_SESSION['n'] = $user_name;
	$_SESSION['is_trader'] = $is_trader;
	
	$rows = $statement->rowCount();
	
	if($rows == 1){
		$ajax_response = true;
		$ajax_message = "Login Successful". $row;
	}
	else{
		$ajax_message = "Unfortunately, there seems to be an issue we our system at the moment, please try again later";
	}
}
else {
	$ajax_message = $row." Could not find the user ".$email." ".$password." ".$user_id;
}

$data_to_send = array('response' => $ajax_response, 'message' => $ajax_message);
echo json_encode($data_to_send);
?>