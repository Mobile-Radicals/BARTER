<?php //this script will be called whenever a new consumer is being created on the server
error_reporting(E_ALL);
ini_set('display_errors', '1');

	require_once '../config/con.php';
	
	

$json = '{"trans_time":"08-08-2013 17:38:08","trans_lat":54.0135272,"trans_origin":"mobile","trader_id":2,"trans_id":1,"trans_price":12,"trans_lon":-2,"trans_points":4,"consumer_id":"041D6D8AF52681","trans_type":"goods"}';

$jsonArray = (json_decode($json));
//var_dump(json_decode($json, true));


//print_r($jsonArray);


//echo $jsonArray->trans_time;

	$results = array(); //used for debugging purposes
	$received = false;
	
	//connect to the database
	try {
		
		//for($i = 0; $i < 2; $i++){
			
			$trader_id = $jsonArray->trader_id;
			$consumer_id = $jsonArray->consumer_id;
			$trans_lat = $jsonArray->trans_lat;
			$trans_lon = $jsonArray->trans_lon;
			$trans_type = $jsonArray->trans_type;
			$trans_origin = $jsonArray->trans_origin;
			$trans_price = $jsonArray->trans_price;
			$trans_points = $jsonArray->trans_points;
			$trans_time = date('Y-m-d H:i:s', strtotime($jsonArray->trans_time));
			
		echo $trader_id." ".$consumer_id." ".$trans_lat." ".$trans_lon." ".$trans_type." ".$trans_origin." ".$trans_price." ".$trans_points." ".$trans_time;
	
			$statement = DB::get()->prepare("INSERT INTO tbl_transactions (trader_id, consumer_id, trans_lat, trans_lon, trans_type, trans_origin, trans_price, trans_points, trans_timestamp) values (:trader_id, :consumer_id, :trans_lat, :trans_lon, :trans_type, :trans_origin, :trans_price, :trans_points, :trans_time)");
			$statement->bindParam(':trader_id', $trader_id, PDO::PARAM_STR);
			$statement->bindParam(':consumer_id', $consumer_id, PDO::PARAM_STR);
			$statement->bindParam(':trans_lat', $trans_lat, PDO::PARAM_STR);
			$statement->bindParam(':trans_lon', $trans_lon, PDO::PARAM_STR);
			$statement->bindParam(':trans_type', $trans_type, PDO::PARAM_STR);
			$statement->bindParam(':trans_origin', $trans_origin, PDO::PARAM_STR);
			$statement->bindParam(':trans_price', $trans_price, PDO::PARAM_STR);
			$statement->bindParam(':trans_points', $trans_points, PDO::PARAM_STR);
			$statement->bindParam(':trans_time', $trans_time, PDO::PARAM_STR);

			if($statement->execute()){
				$received = true;
			}
			else{
				$received = false;
				array_push($results, $jsonArray->trans_id . " - " . $trader_id . " - " . $consumer_id . " - " . $trans_lat . " - " . $trans_lon . " - " . $trans_type . " - " . $trans_origin . " - " . $trans_price . " - " . $trans_points . " - " . $trans_time);
			}
		//}
	}
	
	catch(PDOException $e){
		echo $e->getMessage();
	}
	
	$allData = array(
		'received' => $received,
		'notEntered' => $jsonArray
	);

	//print(json_encode($allData));
	
?>