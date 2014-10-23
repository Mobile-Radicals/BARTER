<?php

	require_once '../config/con.php';
	require_once '../config/utils.php';
	
	//connect to the database
	try {
		$json = json_decode(file_get_contents('php://input'));
		$user_pass = $json->password; 
		$user_email = $json->email;
		$user_pass = salt_password($user_pass); //re-salting the password - safety measures
		$traderData = array();
		$stats = array();
		$consumerData = array();
	
		
		//$user_pass = salt_password(salt_password('g00gle'));
		//$user_email = 'marklochrie50265@gmail.com';
		
		$tid = 0;
		
		//set the status
		$status = 1;
		
		//check if the trader is already a registered trader or
		$stmt = DB::get()->prepare("SELECT * FROM tbl_users WHERE user_email=:email AND user_pass=:password AND user_account_status=:status");
		$stmt->bindParam(':email', $user_email, PDO::PARAM_STR);
		$stmt->bindParam(':password', $user_pass, PDO::PARAM_STR);
		$stmt->bindParam(':status', $status, PDO::PARAM_INT);
		$stmt->execute();
		
		//set the fetch mode  
		$stmt->setFetchMode(PDO::FETCH_OBJ);
		
		$row = $stmt->rowCount();
		
		if($row == 1){
			//we have a correct user login detected
			$message = true;
			
			while($row = $stmt->fetch()){
				//get all the data e need for sending to the app
				
				$tid = $row->user_card_id;
				
				$traderData = array(
					'id' => $row->user_id,
					'name' => $row->user_name,
					'cardId' => $row->user_card_id,
					'gender' => $row->user_gender,
					'dob' => $row->user_dob,
					'postcode' => $row->user_postcode,
					'preferences' => $row->user_ethical_pref,
					'isManufacturer' => $row->is_manufacturer,
					'isRetailer' => $row->is_retailer,
					'isService' => $row->is_service,
					'fixed' => $row->is_fixed_trader,
					'nomadic' => $row->is_non_fixed_trader,
					'goodsServices' => $row->goods_services,
					'statement' => $row->statement
				);
			}
			
			//do another call to get the current stats of the user


			$goods = "goods";
			$services = "services";
			$both = "both";
			$mobile_nfc = "mobile_nfc";
			$mobile_qr = "mobile_qr";
			$mobile_man = "mobile_manual";
			$web = "web_manual";
			$nb = "local_non_barter";
			$nl = "external";

			//get the consumer_rfid from the databas
			$stmt = DB::get()->prepare("SELECT count(trader_id) as number_of_transactions, 
			(SELECT IFNULL(sum(trans_price),0) FROM tbl_transactions WHERE trader_id=:trader_id AND trans_type=:trans_type_goods) as total_trans_goods,
			(SELECT IFNULL(sum(trans_price),0) FROM tbl_transactions WHERE trader_id=:trader_id AND trans_type=:trans_type_services) as total_trans_services,
			(SELECT IFNULL(sum(trans_price),0) FROM tbl_transactions WHERE trader_id=:trader_id AND trans_type=:trans_type_both) as total_trans_both,
			(SELECT IFNULL(sum(trans_price),0) FROM tbl_transactions WHERE trader_id=:trader_id AND consumer_type=:trans_non_barter) as total_non_barter_trans,
			(SELECT IFNULL(sum(trans_price),0) FROM tbl_transactions WHERE trader_id=:trader_id AND consumer_type=:trans_non_local) as total_non_local_trans,
			(SELECT IFNULL(count(trader_id),0) FROM tbl_transactions WHERE trader_id=:trader_id AND trans_origin=:trans_origin_mobile1 OR trans_origin=:trans_origin_mobile2 OR trans_origin=:trans_origin_mobile3) as total_mobile_trans,
			(SELECT IFNULL(count(trader_id),0) FROM tbl_transactions WHERE trader_id=:trader_id AND trans_origin=:trans_origin_web) as total_web_trans,
			(SELECT upload_timestamp FROM tbl_transactions WHERE trader_id=:trader_id ORDER BY trans_id DESC LIMIT 1) as last_uploaded
			FROM tbl_transactions WHERE trader_id=:trader_id");
			$stmt->bindParam(':trader_id', $tid, PDO::PARAM_STR);
			$stmt->bindParam(':trans_type_goods', $goods, PDO::PARAM_STR);
			$stmt->bindParam(':trans_type_services', $services, PDO::PARAM_STR);
			$stmt->bindParam(':trans_type_both', $both, PDO::PARAM_STR);
			$stmt->bindParam(':trans_origin_mobile1', $mobile_nfc, PDO::PARAM_STR);
			$stmt->bindParam(':trans_origin_mobile2', $mobile_qr, PDO::PARAM_STR);
			$stmt->bindParam(':trans_origin_mobile3', $mobile_man, PDO::PARAM_STR);
			$stmt->bindParam(':trans_origin_web', $web, PDO::PARAM_STR);
			$stmt->bindParam(':trans_non_barter', $nb, PDO::PARAM_STR);
			$stmt->bindParam(':trans_non_local', $nl, PDO::PARAM_STR);
			$stmt->execute();
			
			$result = $stmt->fetchAll();
			 
			 if ($result[0][8] == NULL)
					$last_updated = "0000-00-00 00:00:00";
				else
					$last_updated = $result[0][8];
					
			//var_dump($result);
			$stats = array(
				//'total_number_trans' => $result[0][0],
				'total_price_goods' => $result[0][1],
				'total_price_services' => $result[0][2],
				'total_price_both' => $result[0][3],
				'total_non_barter_trans' => $result[0][4],
				'total_non_local_trans' => $result[0][5],
				'total_mobile_trans' => $result[0][6],
				'total_web_trans' => $result[0][7],
				'last_uploaded' => $last_updated
			);
			
			
			
			//get summed transactions for each customer for a specified trader
			$stmt1 = DB::get()->prepare("SELECT *  FROM tbl_customer_totals WHERE trader_id=:tid");
			$stmt1->bindParam(':tid', $tid, PDO::PARAM_STR);
			$stmt1->execute();
				
			//setting the fetch mode  
			$rows = $stmt1->fetchAll(PDO::FETCH_ASSOC);
			$row1 = $stmt1->rowCount();
			
			//$consumerData = array();
			if ($row1 != 0)
			{
				foreach ($rows as $item) 
				{
					/*$consumerData = array(
					'customer_id' => $item['customer_id'],
						'customer_spend' => $item['customer_spend'],
						'customer_points' => $item['customer_points'],
						'customer_occurrences' => $item['customer_occurrences'],
						'timestamp' => $item['timestamp']
				);*/
					$transaction = array(
						'customer_id' => $item['customer_id'],
						'customer_spend' => $item['customer_spend'],
						'customer_points' => $item['customer_points'],
						'customer_occurrences' => $item['customer_occurrences'],
						'timestamp' => $item['timestamp']
					);
					//array_push($transaction, $item['customer_id'],$item['customer_spend'],$item['customer_points'],$item['customer_occurrences'],$item['timestamp']);
					array_push($consumerData,$transaction);
				}
			}	
		}
		else{
			//no user detected or other type of error
			$message = false;
		}
	}
	catch(PDOException $e){
		$received = false;
	}	

	//close the database connection
	$dbh = null;
	
	$allData = array(
		'message' => $message,
		'details' => $traderData,
		'traderTotals' => $stats,
		'customerData' => $consumerData
	);
	
	print json_encode($allData);

?>