<?php

	require_once '../config/con.php';
	
	$jsonArray = array();
	$jsonArray = json_decode(file_get_contents('php://input'));
	$total = sizeof($jsonArray);
	
	$results = array(); //used for debugging purposes
	$received = false; //hep
	
	//connect to the database
	try {
		for($i = 0; $i < $total; $i++){
			$trader_id = $jsonArray[$i]->trader_id;
			$consumer_id = $jsonArray[$i]->consumer_id;
			$redeem_type = $jsonArray[$i]->redeem_type;
			$points_deducted = $jsonArray[$i]->points_deducted;
			$redeem_timestamp = date('Y-m-d H:i:s', strtotime($jsonArray[$i]->redeem_timestamp));			
	
			$stmt = DB::get()->prepare("INSERT INTO tbl_redeems (trader_id, consumer_id, redeem_type, consumer_points_deducted, redeem_timestamp) VALUES (:trader_id, :consumer_id, :redeem_type, :points_deducted, :redeem_timestamp)");
			$stmt->bindParam(':trader_id', $trader_id, PDO::PARAM_INT);
			$stmt->bindParam(':consumer_id', $consumer_id, PDO::PARAM_STR);
			$stmt->bindParam(':redeem_type', $redeem_type, PDO::PARAM_STR);
			$stmt->bindParam(':points_deducted', $points_deducted, PDO::PARAM_STR);
			$stmt->bindParam(':redeem_timestamp', $redeem_timestamp, PDO::PARAM_STR);

			if($stmt->execute()){
				$received = true;
			}
			else{
				$received = false;
				array_push($results, $jsonArray[$i]->redeem_id);
			}
		}
	}
	
	catch(PDOException $e){
		echo $e->getMessage();
	}
	
	$allData = array(
		'received' => $received,
		'notEntered' => $total
	);

	print(json_encode($allData));
	
?>