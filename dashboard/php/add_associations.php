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
$id_input = mysql_escape_string($_REQUEST['ids']);

//split the whole input into separate ids
$ids = explode(",",$id_input);

$count = 0;

$ajax_message = "";
$ids_added = "";

foreach ($ids as $id)
{
	if ($id != "")
	{
		//add new assoc
		$statement = DB::get()->prepare("INSERT IGNORE INTO tbl_associations (trader_id, user_id, timestamp) values
		(:tid, :cid, :timestamp)");
		$statement->bindParam(':tid', $tid, PDO::PARAM_STR);
		$statement->bindParam(':cid', $id, PDO::PARAM_STR);
		$statement->bindParam(':timestamp', $date, PDO::PARAM_STR);
		
		$ajax_response = true;
		//the number of insertions matched the number of ids inputted by the user
		if ($statement->execute())
		{
			$ajax_message = "Associations Added";
		}
		else	
		{
			$ajax_response = false;
			$ajax_message = "Error: Sorry at this time we could not add your associations";
		}
	}
	else
	{
		$ajax_response = false;
		$ajax_message = "Error: Sorry at this time we could not add your associations";
	}
}

$html_data = get_associations($tid);
/*
//get the consumer_rfid from the databas
$stmt = DB::get()->prepare("SELECT a.user_id, user_name FROM tbl_associations AS a LEFT JOIN tbl_users ON a.user_id=tbl_users.user_card_id WHERE trader_id=:tid");
$stmt->bindParam(':tid', $tid, PDO::PARAM_STR);
$stmt->execute();
	
//setting the fetch mode  
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->bindColumn('user_id', $uid);
$stmt->bindColumn('user_name', $uname);
$stmt->fetch(PDO::FETCH_BOUND);
$row = $stmt->rowCount();
	
//$assoc_users = array();

$html_data = "
<h2>Linked Cards</h2>
<table id='assocs' class='table table-bordered table-hover'>";
		
if ($row != 0)
{
	$html_data .= "<tr><th>User Name</th><th>Card ID</th></tr>";
	foreach ($rows as $item) 
	{
		//$assoc_user = array();
		//array_push($assoc_user, $item['user_id'],$item['user_name']);
		//array_push($assoc_users,$assoc_user);
		if ($item['user_name'] == "")
		{
			$item['user_name'] = "Information Unknown";
		}
		$html_data .= "<tr><td><a href='#' target='_blank'>".$item['user_name']."</a></td><td>".$item['user_id']."</td></tr>";
	}
}	
	
$html_data .= "</table>";	*/
	
$data_to_send = array('response' => $ajax_response, 'message' => $ajax_message, 'assocs' => $html_data);
echo json_encode($data_to_send);

	

?>