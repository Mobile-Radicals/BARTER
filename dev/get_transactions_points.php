<?php
header('Content-type: text/xml');
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require '../config/con.php';
//require_once("../config/utils.php");

//$transactions = get_transaction_points();
//print_r ($transactions);

$d = new DomDocument('1.0', 'UTF-8');
$markers = $d->createElement('markers'); 
 
$stmt = DB::get()->prepare(
"SELECT user_card_id, business_name, user_business_lat,user_business_lon FROM tbl_users 
WHERE is_trader = '1' AND user_business_lat != '0'");
$stmt->execute();	
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$row = $stmt->rowCount();		

foreach ($rows as $item) 
{

	$marker = $d->createElement('marker');
	
	$name = $d->createAttribute('name');
	$name->value = $item['business_name'];
	$marker->appendChild($name);
	
	$id = $d->createAttribute('card_id');
	$id->value = $item['user_card_id'];
	$marker->appendChild($id);

	$lat = $d->createAttribute('lat');
	$lat->value = $item['user_business_lat'];
	$marker->appendChild($lat);
	
	$lon = $d->createAttribute('lng');
	$lon->value = $item['user_business_lon'];
	$marker->appendChild($lon);
	
	$icon = $d->createAttribute('icon');
	$icon->value = "img/trader_small_bg.png";
	$marker->appendChild($icon);
	
	$colour = $d->createAttribute('color');
	$colour->value = "#8cba3e";
	$marker->appendChild($colour);
	
	$stmt1 = DB::get()->prepare("SELECT DISTINCT trader_id, consumer_id,uc.business_name,uc.user_business_lat,uc.user_business_lon
			FROM tbl_transactions AS t
			LEFT JOIN tbl_users AS uc
			ON t.consumer_id=uc.user_card_id WHERE t.trader_id =:id");
			
			
	$stmt1->bindParam(':id', $item['user_card_id'], PDO::PARAM_STR);
	$stmt1->execute();	
	
	$rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
	$row1 = $stmt1->rowCount();

	if ($row1 != 0)
	{
		$connectionsStatus = $d->createAttribute('connections');
		$connectionsStatus->value = "true";
		$marker->appendChild($connectionsStatus);
	
		$noConnections = $d->createAttribute('number_of_connections');
		$noConnections->value = $row1;
		$marker->appendChild($noConnections);
		
		foreach ($rows1 as $item1) 
		{
			$connection = $d->createElement('connection');
			$marker->appendChild($connection);
			
			$lat1 = $d->createAttribute('lat');
			$lat1->value = $item1['user_business_lat'];
			$connection->appendChild($lat1);
			
			$lon1 = $d->createAttribute('lng');
			$lon1->value = $item1['user_business_lon'];
			$connection->appendChild($lon1);
		}
	}
	else
	{
		$connectionsStatus = $d->createAttribute('connections');
		$connectionsStatus->value = "false";
		$marker->appendChild($connectionsStatus);
		
		$noConnections = $d->createAttribute('number_of_connections');
		$noConnections->value = '0';
		$marker->appendChild($noConnections);
	}
	
	//echo "<br />";
	$markers->appendChild($marker);
}
$d->appendChild($markers);
echo $d->saveXML();
?>