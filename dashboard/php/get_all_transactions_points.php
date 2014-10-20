<?php
header('Content-type: text/xml');
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require '../../config/con.php';
//require_once("../config/utils.php");

//$transactions = get_transaction_points();
//print_r ($transactions);

$d = new DomDocument('1.0', 'UTF-8');
$markers = $d->createElement('markers'); 
 
$stmt = DB::get()->prepare(
"SELECT * FROM tbl_transactions WHERE trans_lat != 0");
$stmt->execute();	
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$row = $stmt->rowCount();		

foreach ($rows as $item) 
{

	$marker = $d->createElement('marker');
	

	$lat = $d->createAttribute('lat');
	$lat->value = $item['trans_lat'];
	$marker->appendChild($lat);
	
	$lon = $d->createAttribute('lng');
	$lon->value = $item['trans_lon'];
	$marker->appendChild($lon);
	
	$price = $d->createAttribute('weight');
	$price->value = $item['trans_price'];
	$marker->appendChild($price);

	
	//echo "<br />";
	$markers->appendChild($marker);
}
$d->appendChild($markers);
echo $d->saveXML();
?>