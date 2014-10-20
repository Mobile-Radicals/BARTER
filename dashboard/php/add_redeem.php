<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require '../../config/con.php';
require '../../config/utils.php';

//get the data
$tid = mysql_escape_string($_REQUEST['tid']);
$cid = mysql_escape_string($_REQUEST['customer_id']);
$pts = mysql_escape_string($_REQUEST['pointsDropDown']);
$rtype = "web_manual";
$date = date('Y-m-d H:i:s');
 
$ajax_response = false;

//add new trans
$statement = DB::get()->prepare("INSERT INTO tbl_redeems (trader_id, consumer_id, redeem_type, consumer_points_deducted, redeem_timestamp, uploaded_timestamp) values
(:tid, :cid, :rtype, :pts, :timestamp, :timestamp1)");
$statement->bindParam(':tid', $tid, PDO::PARAM_STR);
$statement->bindParam(':cid', $cid, PDO::PARAM_STR);
$statement->bindParam(':rtype', $rtype, PDO::PARAM_STR);
$statement->bindParam(':pts', $pts, PDO::PARAM_STR);
$statement->bindParam(':timestamp', $date, PDO::PARAM_STR);
$statement->bindParam(':timestamp1', $date, PDO::PARAM_STR);
//execute the query
if($statement->execute())
{	
	$ajax_response = true;
	$id = DB::get()->lastInsertId();
	$ajax_message = "Redeem ".$id.": successfully added.";
}
else	
{
	$ajax_response = false;
	$ajax_message = "There seems to be an issue with our system at the moment, please try again later or alternativly contact us on info@barterproject.org";
}

$data_to_send = array('response' => $ajax_response, 'message' => $ajax_message);
echo json_encode($data_to_send);
?>