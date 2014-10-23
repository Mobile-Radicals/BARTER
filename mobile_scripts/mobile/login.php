<?php

	//include the database class
	require_once '../config/con.php';
	require_once '../config/utils.php';
	
	$customerData = array();
	$message_logged_in = FALSE;
	
	//connect to the database
	try {
		$user_email = $_REQUEST['email'];
		$user_pass = salt_password($_REQUEST['password']);
		$user_pass = salt_password($user_pass);
		$type = "customer";
		//define the handling message
		
		//echo $user_email . "" . $user_pass;
		
		
	
		//check if the trader is already a registered trader or
		$stmt = DB::get()->prepare("SELECT * FROM tbl_users WHERE user_email=:email AND user_pass=:password AND user_type=:type");
		$stmt->bindParam(':email', $user_email, PDO::PARAM_STR);
		$stmt->bindParam(':password', $user_pass, PDO::PARAM_STR);
		$stmt->bindParam(':type', $type, PDO::PARAM_INT);
		$stmt->execute();
	
		//set the fetch mode  
		$stmt->setFetchMode(PDO::FETCH_OBJ);
		
		$row = $stmt->rowCount();
			
		if($row == 1){
			//we have a correct user login detected
			$message_logged_in = true;
			
			while($row = $stmt->fetch()){
				//get all the data e need for sending to the app
				$customerData = array(
					'id' => $row->user_id,
					'name' => $row->user_name,
					'cardId' => $row->user_card_id,
					'preferences' => $row->user_ethical_pref
				);
			}
		}
		else{
			//no user detected or other type of error
			$message_logged_in = false;
			$customerData = null;
		}
	}
	catch(PDOException $e){
		$message_logged_in = false;
		$customerData = null;
	}	
	
	//close the database connection
	$dbh = null;
	
	$allData = array(
		'message' => $message_logged_in,
		'data' => $customerData
	);
	
	echo json_encode($allData);
?>