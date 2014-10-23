<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
//include the database class
require '../../config/con.php';

//get the consumer_rfid from the databas
$stmt = DB::get()->prepare("SELECT user_name FROM tbl_users WHERE user_name=:username LIMIT 1");
$stmt->bindParam(':username', $_REQUEST["value"], PDO::PARAM_STR);
$stmt->execute();
	
//setting the fetch mode  
$stmt->setFetchMode(PDO::FETCH_ASSOC); 
$row = $stmt->rowCount();

if ($row == 0)
{
	echo json_encode(
		array(
		  "value" => $_REQUEST["value"],
		  "valid" => !preg_match("/\\s/", $_REQUEST["value"]),
		  "message" => "You can not have a space in your username."
		)
	  );
}
else
{
	$result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
	$name = $result[0];

	echo json_encode(
		array(
		  "value" => $_REQUEST["value"],
		  "valid" => preg_match("/[^$name$]/", $_REQUEST["value"]),
		  "message" => "Sorry, that username is already taken."
		)
	  );
}

/*if ($_REQUEST["value"] == 'mark')
{
	echo json_encode(
    array(
      "value" => $_REQUEST["value"],
      "valid" => preg_match("/^\S*$/", $_REQUEST["value"]),
      "message" => "The username you have entered has already been taken"
    )
  );
}
else
{
	echo json_encode(
    array(
      "value" => $_REQUEST["value"],
      "valid" => preg_match("/^\S*$/", $_REQUEST["value"]),
      "message" => "Your username must not include a space & be a minimum of 6 characters in length"
    )
  );
}*/




?>