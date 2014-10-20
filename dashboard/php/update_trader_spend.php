<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
//include the database class
require '../../config/con.php';
require '../../config/utils.php';


$date = date('Y-m-d H:i:s');
 
$ajax_response = false;

//get the data
$tid = mysql_escape_string($_REQUEST['trader_id']);
$local_spend = mysql_escape_string($_REQUEST['local_spend']);
$non_local_spend = mysql_escape_string($_REQUEST['non_local_spend']);

$ajax_message = "";

//add new assoc
/*$statement = DB::get()->prepare("INSERT IGNORE INTO tbl_trader_snapshots (trader_card_id, trader_local_spend, trader_non_local_spend, timestamp) VALUES
(:tid, :ls, :nls, :t)  ON DUPLICATE KEY UPDATE trader_local_spend=:ls, trader_non_local_spend=:nls, timestamp=:t");*/
$statement = DB::get()->prepare("INSERT INTO tbl_trader_snapshots (trader_card_id, trader_local_spend, trader_non_local_spend, timestamp) VALUES
(:tid, :ls, :nls, :t)");
$statement->bindParam(':tid', $tid, PDO::PARAM_STR);
$statement->bindParam(':ls', $local_spend, PDO::PARAM_STR);
$statement->bindParam(':nls', $non_local_spend, PDO::PARAM_STR);
$statement->bindParam(':t', $date, PDO::PARAM_STR);

//the number of insertions matched the number of ids inputted by the user
if ($statement->execute())
{
	$date = convert_to_date($date);
	$ajax_response = true;
	$ajax_message = "Spend Updated";
	$data = array(
		'local_spend' => $local_spend,
		'non_local_spend' => $non_local_spend,
		'timestamp' => $date
		);
}
else	
{
	$ajax_response = false;
	$ajax_message = "Error: Sorry at this time we could not update your local spend";
}
	


$data_to_send = array('response' => $ajax_response, 'message' => $ajax_message, 'data'=> $data);
echo json_encode($data_to_send);

	

?>