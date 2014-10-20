<?php
//include the database class
require 'con.php';
require 'utils.php';
session_name('barter');
session_start();

// Prints the day, date, month, year, time, AM or PM
//$part_of_day = date("A");
$part_of_day = date("G");
if ($part_of_day>=0 && $part_of_day<=11 ) {
  $part_of_day = "Good Morning";
}
elseif ($part_of_day>=12 && $part_of_day<=17) {
  $part_of_day = "Good Afternoon";
}
elseif ($part_of_day>=18) {
  $part_of_day = "Good Evening";
}

$page = curPageURL();
	


if (isset($_SESSION['uid'])) 
{
	$uid = $_SESSION['uid'];
	$e = $_SESSION['e'];
	$p = $_SESSION['p'];
	$name = $_SESSION['n'];
	$is_trader = $_SESSION['is_trader'];
	
	if ($is_trader == "1"){$user_type = "Trader";}else{$user_type = "Consumer";}
	
	//get the consumer_rfid from the databas
	$stmt = DB::get()->prepare("SELECT * FROM tbl_users WHERE user_id=:id");
	$stmt->bindParam(':id', $uid, PDO::PARAM_STR);
	$stmt->execute();
		
	//setting the fetch mode  
	$stmt->setFetchMode(PDO::FETCH_ASSOC); 
	$row = $stmt->rowCount();
	
	$stmt->bindColumn('user_card_id', $card_id);
	$stmt->bindColumn('last_login', $last_login);
	$stmt->fetch(PDO::FETCH_BOUND);
	
	$last_login = strtotime($last_login);
	$last_login = date('d-m-Y H:i:s', $last_login); 

	//echo $page;
	if ($page == 'http://barterproject.org/dashboard/')
	{
		header('Location: http://barterproject.org/dashboard/signin.php');
	}
	/*else if  ($page == 'http://barterproject.org/dashboard/dashboard.php')
	{
		header('Location: http://barterproject.org/dashboard/dashboard.php');
	}
    else
    {
   		// echo "ok";
    }*/
}
else
{
	if ($page == 'http://barterproject.org/dashboard/')
	{
		//echo "here1 ".$page;
	}
	else if  ($page == 'http://barterproject.org/dashboard/signin.php')
	{
		//echo "here2 ".$page;
	}
    else
    {
   		 header('Location: http://barterproject.org/dashboard/signin.php');
    }
}


?>