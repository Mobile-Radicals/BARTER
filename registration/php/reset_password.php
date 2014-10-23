<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//include the database class
require '../../config/con.php';
require '../../config/utils.php';

//get the data
$rfid = mysql_escape_string($_REQUEST['card_id']);
$email = mysql_escape_string($_REQUEST['email']);
$user_id = mysql_escape_string($_REQUEST['user_id']);
$pass_key = mysql_escape_string($_REQUEST['pass_key']);


//get the consumer_rfid from the databas
$stmt = DB::get()->prepare("SELECT user_id, user_name, user_email, user_card_id,business_name FROM tbl_users WHERE user_id=:uid AND pass_key=:pk AND user_card_id=:rfid AND user_email=:email");
$stmt->bindParam(':uid', $user_id, PDO::PARAM_STR);
$stmt->bindParam(':pk', $pass_key, PDO::PARAM_STR);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':rfid', $rfid, PDO::PARAM_STR);
$stmt->execute();

//setting the fetch mode  
$stmt->setFetchMode(PDO::FETCH_ASSOC); 
$row = $stmt->rowCount();

/* Bind by column name */
$stmt->bindColumn('user_name', $account_name);
$stmt->bindColumn('business_name', $business);
$stmt->bindColumn('user_card_id', $rfid);
$stmt->fetch(PDO::FETCH_BOUND);


//found the user	
$ajax_response = false;

	if($row == 1){ 
	
		$status = 0;
		$blank = "";
		$new_pass_key_to_send = generate_password();
		$new_pass_key = salt_password($new_pass_key_to_send);
//used for testing
//$new_pass_key = $pass_key;

		$date = new DateTime();
		$date->getTimestamp();
		$now = $date->format('Y-m-d H:i:s');
		
		$action = "request_reset_password";
			
		$statement = DB::get()->prepare("UPDATE tbl_users SET user_pass=:blank, user_account_status=:pending, pass_key=:new_pk,last_updated=:updated, last_action=:action WHERE user_id=:id AND user_email=:email");
		$statement->bindParam(':blank', $blank, PDO::PARAM_STR);
		$statement->bindParam(':id', $user_id, PDO::PARAM_STR);
		$statement->bindParam(':new_pk', $new_pass_key, PDO::PARAM_STR);
		$statement->bindParam(':pending', $status, PDO::PARAM_INT);
		$statement->bindParam(':email', $email, PDO::PARAM_STR);
		$statement->bindParam(':updated', $now, PDO::PARAM_STR);
		$statement->bindParam(':action', $action, PDO::PARAM_STR);
		$statement->execute();
		
		$rows = $statement->rowCount();
		
		if($rows == 1){
			sendEmail($account_name, $email, $new_pass_key, '0', 'Reset Password',$rfid, $business);
	
			$ajax_response = true;
			$ajax_message = "You should received an email with instructions on how to reset your password.";
		}
		else{
			$ajax_message = "Unfortunately, there seems to be an issue we our system at the moment, please try again later";
		}
	}
	else {
		$ajax_message = "We were unable to reset your password at this time.";
	}

	$data_to_send = array('response' => $ajax_response, 'message' => $ajax_message);
	echo json_encode($data_to_send);
	
	

?>