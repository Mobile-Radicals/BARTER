<?php //this script will be called whenever a new consumer is being created on the server

	require_once '../config/con.php';
	
	$jsonArray = array();
	$jsonArray = json_decode(file_get_contents('php://input'));
	$total = sizeof($jsonArray);
	
	$results = array(); //used for debugging purposes
	$received = false;
	
	//connect to the database
	try {
		for($i = 0; $i < $total; $i++){
			$trader_id = $jsonArray[$i]->trader_id;
			$consumer_id = $jsonArray[$i]->consumer_id;
			$trans_lat = $jsonArray[$i]->trans_lat;
			$trans_lon = $jsonArray[$i]->trans_lon;
			$trans_type = $jsonArray[$i]->trans_type;
			$trans_origin = $jsonArray[$i]->trans_origin;
			$trans_price = $jsonArray[$i]->trans_price;
			$trans_points = $jsonArray[$i]->trans_points;
			$trans_time = date('Y-m-d H:i:s', strtotime($jsonArray[$i]->trans_time));
	
			$stmt = DB::get()->prepare("INSERT INTO tbl_transactions (trader_id, consumer_id, trans_lat, trans_lon, trans_type, trans_origin, trans_price, trans_points, trans_timestamp) values (:trader_id, :consumer_id, :trans_lat, :trans_lon, :trans_type, :trans_origin, :trans_price, :trans_points, :trans_time)");
			$stmt->bindParam(':trader_id', $trader_id, PDO::PARAM_INT);
			$stmt->bindParam(':consumer_id', $consumer_id, PDO::PARAM_STR);
			$stmt->bindParam(':trans_lat', $trans_lat, PDO::PARAM_STR);
			$stmt->bindParam(':trans_lon', $trans_lon, PDO::PARAM_STR);
			$stmt->bindParam(':trans_type', $trans_type, PDO::PARAM_STR);
			$stmt->bindParam(':trans_origin', $trans_origin, PDO::PARAM_STR);
			$stmt->bindParam(':trans_price', $trans_price, PDO::PARAM_STR);
			$stmt->bindParam(':trans_points', $trans_points, PDO::PARAM_STR);
			$stmt->bindParam(':trans_time', $trans_time, PDO::PARAM_STR);
			
			$id = DB::get()->lastInsertId();
		$stmt1 = DB::get()->prepare("call InsertTransaction($id)");
		$stmt1->execute();
			
			if($stmt->execute()){
				$received = true;
				
				$num = 1;
				$updated = "mobile";
				//update totals tbl
				$statement = DB::get()->prepare("INSERT INTO tbl_customer_totals (trader_id, customer_id, customer_spend, customer_points, customer_occurrences, updated_by, timestamp) values (:tid, :cid, :spend, :pts, :number, :updated, :timestamp)
				ON DUPLICATE KEY UPDATE customer_spend=:spend+customer_spend, `customer_points`=:pts+customer_points, `customer_occurrences`=:number+customer_occurrences, updated_by=:updated,timestamp=:timestamp");
				$statement->bindParam(':tid', $trader_id, PDO::PARAM_STR);
				$statement->bindParam(':cid', $consumer_id, PDO::PARAM_STR);
				$statement->bindParam(':spend', $trans_price, PDO::PARAM_STR);
				$statement->bindParam(':pts', $trans_points, PDO::PARAM_STR);
				$statement->bindParam(':number', $num, PDO::PARAM_STR);
				$statement->bindParam(':updated', $updated, PDO::PARAM_STR);
				$statement->bindParam(':timestamp', $trans_time, PDO::PARAM_STR);
				if($statement->execute())
				{
					$ajax_message = "t:".$id." and total updated";
				}
				else
				{
					$ajax_message = "t:".$id." but total not updated";
				}
			}
			else{
				$received = false;
				array_push($results, $jsonArray[$i]->trans_id);
			}
		}
	}
	
	catch(PDOException $e){
		echo $e->getMessage();
	}
	
	$allData = array(
		'received' => $received,
		'notEntered' => $jsonArray
	);

	print(json_encode($allData));
	
?>