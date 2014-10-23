<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//include the database class
require '../../config/con.php';
require '../../config/utils.php';

//get the data
$user_id = mysql_escape_string($_REQUEST['user_id']);
$password = mysql_escape_string($_REQUEST['password1']);
$password = salt_password($password);

//get the value of the key
//$pass_key = substr($mysql_escape_string($_REQUEST['pass_key']), 40);
$pass_key = $_REQUEST['pass_key'];

//get the consumer_rfid from the databas
$stmt = DB::get()->prepare("SELECT user_id, is_trader FROM tbl_users WHERE user_id=:uid AND pass_key=:pk");
$stmt->bindParam(':uid', $user_id, PDO::PARAM_STR);
$stmt->bindParam(':pk', $pass_key, PDO::PARAM_STR);
$stmt->execute();
	
//setting the fetch mode  
$stmt->setFetchMode(PDO::FETCH_ASSOC); 
$row = $stmt->rowCount();

$stmt->bindColumn('is_trader', $is_trader);
$stmt->fetch(PDO::FETCH_BOUND);

//found the user	
$ajax_response = false;
$url = "http://barterproject.org/";

	if($row == 1){ 
	
		if ($is_trader == '1')
		{
			$url = "http://barterproject.org/dashboard/signin.php";
			$status = 1;
			$success_message = "Great you are all setup now, please feel free to login and explore project BARTER";
		}else{
			$status = 0;
			$success_message = "Great you are all signed up, currently we are still preparing the customer dashboard, we will inform you when it's ready. We really do appreciate your participation";
		}
		$blank = "";	
		$date = new DateTime();
		$date->getTimestamp();
		$now = $date->format('Y-m-d H:i:s');
		
		$action = "verified_account";
		
		//get the insert password and save it to the database
		$password = salt_password($password);
		$statement = DB::get()->prepare("UPDATE tbl_users SET user_pass=:p, user_account_status=:ok, last_updated=:updated, last_action=:action WHERE user_id=:id AND pass_key=:pk AND user_pass =:blank");
		$statement->bindParam(':p', $password, PDO::PARAM_STR);
		$statement->bindParam(':id', $user_id, PDO::PARAM_STR);
		$statement->bindParam(':pk', $pass_key, PDO::PARAM_STR);
		$statement->bindParam(':ok', $status, PDO::PARAM_INT);
		$statement->bindParam(':blank', $blank, PDO::PARAM_STR);
		$statement->bindParam(':updated', $now, PDO::PARAM_STR);
		$statement->bindParam(':action', $action, PDO::PARAM_STR);
		$statement->execute();
		$rows = $statement->rowCount();
		
		
		if($rows == 1){
			//sendEmail($account_name, $email, $pass_key, $account);
			$ajax_response = true;
			$ajax_message = $success_message;
		}
		else{
			$ajax_message = "Unfortunately, there seems to be an issue we our system at the moment, please try again later";
		}
	}
	else {
		$ajax_message = "Could not update your credentials.";
	}

	$data_to_send = array('response' => $ajax_response, 'message' => $ajax_message, 'redirect'=> $url);
	echo json_encode($data_to_send);
	
	

?>