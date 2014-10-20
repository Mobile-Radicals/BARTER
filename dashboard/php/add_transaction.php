<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

//debug statements
//consumer
//card_id=12345678&account=consumer&account_name=Mark+Lochrie&username=marklochrie&password1=mypass1&password2=mypass1&email=marklochrie50265%40gmail.com&gender=M&postcode=LA1+4DG&ethical_pref=environmental_impact&goods_services=&statement=&service=false&manufacturer=false&retailer=false&fixed=false&normadic=false&dob=19-08-1986 

//trader
//
	
//include the database class
require '../../config/con.php';
require '../../config/utils.php';

//get the data
$tid = mysql_escape_string($_REQUEST['trader_id']);
$price = mysql_escape_string($_REQUEST['price']);
$cid = mysql_escape_string($_REQUEST['consumer_id']);
$cname = mysql_escape_string($_REQUEST['customer_name']);
$ctype = mysql_escape_string($_REQUEST['customer_type']);

if ($ctype == "0")
{
	$ctype = "barter";
}
else if ($ctype == "1")
{
	$cid = $cname;
	$cname = "";
	$ctype = "barter";
}
else if ($ctype == "2")
{
	$cid = 0;
	$ctype = "local_non_barter";
}
else if ($ctype == "3")
{
	$cid = 0;
	$ctype = "external";
}	

$pts = mysql_escape_string($_REQUEST['points']);
$is_goods = mysql_escape_string($_REQUEST['is_goods']);
$is_service = mysql_escape_string($_REQUEST['is_service']);

$lat = mysql_escape_string($_REQUEST['lat']);
$lon = mysql_escape_string($_REQUEST['lon']);


if ($is_goods == 1 && $is_service == 1)
{
	$type = 'both';
}
else if ($is_goods == 1)
{
	$type = 'goods';
}
else if ($is_service == 1)
{
	$type = 'services';
}

$origin = 'web_manual';

$date = date('Y-m-d H:i:s');
 
$ajax_response = false;


//add new trans
$statement = DB::get()->prepare("INSERT INTO tbl_transactions (trader_id, consumer_id, consumer_type, consumer_name, trans_lat, trans_lon, trans_type, trans_origin,trans_price, trans_points,  trans_timestamp) values
(:tid, :cid, :ctype, :cname, :lat, :lon, :type, :origin,  :price, :points, :timestamp)");
$statement->bindParam(':tid', $tid, PDO::PARAM_STR);
$statement->bindParam(':cid', $cid, PDO::PARAM_STR);
$statement->bindParam(':ctype', $ctype, PDO::PARAM_STR);
$statement->bindParam(':cname', $cname, PDO::PARAM_STR);
$statement->bindParam(':lat', $lat, PDO::PARAM_STR);
$statement->bindParam(':lon', $lon, PDO::PARAM_STR);
$statement->bindParam(':type', $type, PDO::PARAM_STR);
$statement->bindParam(':origin', $origin, PDO::PARAM_STR);
$statement->bindParam(':price', $price, PDO::PARAM_STR);
$statement->bindParam(':points', $pts, PDO::PARAM_STR);
$statement->bindParam(':timestamp', $date, PDO::PARAM_STR);

//execute the query
if($statement->execute())
{	
	$ajax_response = true;

	$id = DB::get()->lastInsertId();
	$ajax_message = "Transaction ".$id.": successfully added.";
	
	$cusType = "barter";
	$cusType1 = "local_non_barter";
	$cusType2 = "external";
		
	$stmt = DB::get()->prepare("SELECT IFNULL(COUNT(trans_id),0) AS total_trans, 
	(SELECT IFNULL(COUNT(trans_id),0) FROM `tbl_transactions` WHERE trader_id=:tid AND consumer_type=:type) AS trader_trans, 
	(SELECT IFNULL(SUM(trans_price),0) FROM tbl_transactions WHERE consumer_type=:type) AS community_spend, (SELECT SUM(trans_price) FROM `tbl_transactions` WHERE trader_id=:tid AND consumer_type=:type) AS my_contributions ,
	(SELECT IFNULL(SUM(trans_price),0) FROM `tbl_transactions` WHERE trader_id=:tid AND consumer_type=:type1) AS my_local_contributions,
	(SELECT IFNULL(SUM(trans_price),0) FROM `tbl_transactions` WHERE trader_id=:tid AND consumer_type=:type2) AS my_non_local_contributions FROM tbl_transactions WHERE consumer_type=:type");
	$stmt->bindParam(':tid', $tid, PDO::PARAM_STR);
	$stmt->bindParam(':type', $cusType, PDO::PARAM_STR);
	$stmt->bindParam(':type1', $cusType1, PDO::PARAM_STR);
	$stmt->bindParam(':type2', $cusType2, PDO::PARAM_STR);
	$stmt->execute();
	
	//setting the fetch mode  
	$stmt->setFetchMode(PDO::FETCH_ASSOC); 
	$row = $stmt->rowCount();

	$stmt->bindColumn('total_trans', $total);
	$stmt->bindColumn('trader_trans', $trader_total);
	$stmt->bindColumn('community_spend', $community_spend);
	$stmt->bindColumn('my_contributions', $my_spend);
	$stmt->bindColumn('my_local_contributions', $my_spend_local);
	$stmt->bindColumn('my_non_local_contributions', $my_spend_non_local);
	$stmt->fetch(PDO::FETCH_BOUND);
	$count = ($trader_total/$total)*100;
	$total = ($my_spend/$community_spend)*100;
	
	$num = 1;
	//update totals tbl
	$statement = DB::get()->prepare("INSERT INTO tbl_customer_totals (trader_id, customer_id, customer_spend, customer_points, customer_occurrences, timestamp) values (:tid, :cid, :spend, :pts, :number, :timestamp)
	ON DUPLICATE KEY UPDATE customer_spend=:spend+customer_spend, `customer_points`=:pts+customer_points, `customer_occurrences`=:number+customer_occurrences");
	$statement->bindParam(':tid', $tid, PDO::PARAM_STR);
	$statement->bindParam(':cid', $cid, PDO::PARAM_STR);
	$statement->bindParam(':spend', $price, PDO::PARAM_STR);
	$statement->bindParam(':pts', $pts, PDO::PARAM_STR);
	$statement->bindParam(':number', $num, PDO::PARAM_STR);
	$statement->bindParam(':timestamp', $date, PDO::PARAM_STR);
	
	$id = DB::get()->lastInsertId();
	$stmt1 = DB::get()->prepare("call InsertTransaction($id)");
	$stmt1->execute();
			
	if($statement->execute())
	{
		$ajax_message = "t:".$id." and total updated";
	}
	else
	{
		$ajax_message = "t:".$id." but total not updated";
	}
}
else	
{
	$ajax_response = false;
	$ajax_message = "There seems to be an issue with our system at the moment, please try again later or alternativly contact us on info@barterproject.org";
}




$data_to_send = array('response' => $ajax_response, 'message' => $ajax_message, 'trans_percentage' => $count, 'contributions'=>$total, 'community_spend'=>$community_spend, 'my_contributions' =>$my_spend, 'my_local_contributions' =>$my_spend_local, 'my_non_local_contributions' =>$my_spend_non_local);
echo json_encode($data_to_send);

	

?>