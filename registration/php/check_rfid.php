<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
//include the database class
require '../../config/con.php';

//get the consumer_rfid from the databas
$stmt = DB::get()->prepare("SELECT user_card_id FROM tbl_users WHERE user_card_id=:card_id LIMIT 1");
$stmt->bindParam(':card_id', $_REQUEST["value"], PDO::PARAM_STR);
$stmt->execute();
	
//setting the fetch mode  
$stmt->setFetchMode(PDO::FETCH_ASSOC); 
$row = $stmt->rowCount();

if ($row == 0)
{
	echo json_encode(
		array(
		  "value" => $_REQUEST["value"],
		  "valid" => preg_match("/^[a-zA-Z0-9]+$/", $_REQUEST["value"]),
		  "message" => "Card ID only accepts letters and numbers. Please enter the numbers printed on the front of your card."
		)
	  );
}
else
{
	$result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
	$rfid = $result[0];

	echo json_encode(
		array(
		  "value" => $_REQUEST["value"],
		  "valid" => preg_match("/[^$rfid$]/", $_REQUEST["value"]),
		  "message" => "Sorry, either that card number is invalid or that card number has previously been registered. "
		)
	  );
}

  

?>