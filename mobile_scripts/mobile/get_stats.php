<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('../config/con.php');

//$tid = $_REQUEST["value"];
$tid = 1;

$goods = "goods";
$services = "services";
$both = "both";

//get the consumer_rfid from the databas
$stmt = DB::get()->prepare("SELECT count(trader_id) as number_of_transactions, 
(SELECT sum(trans_price) FROM tbl_transactions WHERE trader_id=:trader_id AND trans_type=:trans_type_goods) as total_trans_goods,
(SELECT sum(trans_price) FROM tbl_transactions WHERE trader_id=:trader_id AND trans_type=:trans_type_services) as total_trans_services,
(SELECT sum(trans_price) FROM tbl_transactions WHERE trader_id=:trader_id AND trans_type=:trans_type_both) as total_trans_both,
(SELECT upload_timestamp FROM tbl_transactions WHERE trader_id=:trader_id ORDER BY trans_id DESC LIMIT 1) as last_uploaded
FROM tbl_transactions WHERE trader_id=:trader_id");
$stmt->bindParam(':trader_id', $tid, PDO::PARAM_STR);
$stmt->bindParam(':trans_type_goods', $goods, PDO::PARAM_STR);
$stmt->bindParam(':trans_type_services', $services, PDO::PARAM_STR);
$stmt->bindParam(':trans_type_both', $both, PDO::PARAM_STR);
$stmt->execute();

$result = $stmt->fetchAll();

$total_trans =  $result[0][0];
$total_price_goods = $result[0][1];
$total_price_services = $result[0][2];
$total_price_both = $result[0][3];
$last_uploaded = $result[0][4];

echo $total_trans." ".$total_price_goods." ".$total_price_services." ".$total_price_both." ".$last_uploaded;
 
?>

