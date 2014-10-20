<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
define("CURRENCY","&pound;");
include_once ("con.php");
if(isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
	$action = $_REQUEST['action'];

	switch($action) 
	{
		case 'getUserPts': 
			echo get_total_points($_REQUEST['customer_id']);
			break;
		case 'getCommunitySpend': 
			get_community_spend($_REQUEST['tid']);
			break;	
		case 'checkSnapshotTimestamp': 
			check_snapshot_timestamp($_REQUEST['cid']);
			break;
		case 'getSystemSpend':
			get_system_spend_by_month($_REQUEST['tid']);
			break;
	}
}

	//simple function to generate a random string of 8 digits length
	function generate_password() {
		$size = '10';
		$string = '';
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		for ($i = 0; $i < $size; $i++)
			$string .= $characters[mt_rand(0, (strlen($characters) - 1))];  
		return $string;
	}
	
	//simple function to salt a given password that returns an md5 hash
	function salt_password($password){
		$to_salt = $password."a7y3ttk7go";
		return md5($to_salt);
	}

	function sendEmail($uname, $to, $pass_key, $account, $subject, $rfid, $business)
	{	
		$headers = "From: hello@barterproject.org <hello@barterproject.org>\r\n";
    	$headers .= "MIME-Version: 1.0\r\n";
    	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "Reply-To: barter project <hello@barterproject.org>\r\n";
		$headers .= "Return-Path: barter project <hello@barterproject.org>\r\n";
		$headers .= "X-Mailer: PHP". phpversion() ."\n";
		$headers .= "X-Originating-IP: [".getenv("REMOTE_ADDR")."]\r\n";
   		$headers .= "X-Sender-IP: " . $_SERVER["REMOTE_ADDR"]."\r\n";  

		if ($subject == "Welcome to BARTER")
		{
			if ($account != "customer")
			{
				$to = 'marklochrie50265@gmail.com, branknowles9@gmail.com';
				$headers = "MIME-Version: 1.0\r\n";
    			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
				$inner_message = '<br />Business/Organisation: <strong>'.$business.' ('.$uname.')</strong> with the card number: <strong>'.$rfid.'</strong> has just signed up to the BARTER project
				<h3>Before the user can access their account you need to verify their credentials and account information</h3>
				<p>Please click the link below and follow the onscreen instructions to verify the users account.</p>
				<a href="http://barterproject.org/admin/php/confirm.php?id='.$rfid.'&key='.$pass_key.'">Verify Account</a>';
			}
			else
			{
				$inner_message = '<h2>Welcome, '.$uname.'</h2>
				<h3>Thank you for signing up to the BARTER project</h3>
				<p>Before we can process your account we need to verify you are real!
				<br />Please click the link below and create a password.</p>
				<a href="http://barterproject.org/registration/verify_account.php?id='.$pass_key.'">Verify Account</a>';
			}
		}
		else if ($subject == "Reset Password")
		{
			$inner_message = '
		<h3>We have reset your account as per request.</h3>
		<p>Please enter a new password.</p>
		<a href="http://barterproject.org/registration/verify_account.php?id='.$pass_key.'">Set Password</a>
		<p>If you did not request to reset your password, please contact us ASAP</p>';
		}
		
		
		$message = '<!DOCTYPE html>
		<html>
		<head>
		<title>BARTER - Registration</title>
		<head>
		<style>
		html
		{
			margin:10;
			padding:10;
		}
		@font-face
		{
			font-family: BARTERfont;
			src: url("http://www.barterproject.org/fonts/b.ttf"),
					url("http://www.barterproject.org/fonts/b.woff");
		}
		h1,h2,h3,h4,a,p
		{
			font-family: BARTERfont;
		}
		
		#container
		{
			background:#EEEEEE;
			width:500px;
			padding:10px;
		}
		
		</style>
		</head>
		<body>
		<div id="container">
		<img id="header_img" src="http://barterproject.org/wp-content/uploads/2013/06/final_logo_full.png" alt="BARTER" />';
		
		$message .= $inner_message;
		
		$message .= '<br /><br />
		</div>
		</body>
		</html>';
		
		//$message->setReturnPath('info@barterproject.org');
		
		if(@mail($to, $subject, $message, $headers, "-f hello@barterproject.org"))
		{
			$is_sent = true;
			return $is_sent;
			//echo "Mail Sent Successfully";
		}
		else{
			$is_sent = false;
			return $is_sent;
			//echo "Mail Not Sent";
		}
 
	}
	
	function curPageURL() 
	{
	 $pageURL = 'http';
	 $pageURL .= "://";
	 if ($_SERVER["SERVER_PORT"] != "80") {
	  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"].substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
	 } else {
	  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	 }
	 return $pageURL;
	}
	
	function curPageName() {
	 return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
	}
	
	
	function user_accounts($e,$p)
	{
		//get the consumer_rfid from the databas
		$stmt = DB::get()->prepare("SELECT * FROM tbl_users WHERE user_email=:e AND user_pass=:p");
		$stmt->bindParam(':e', $e, PDO::PARAM_STR);
		$stmt->bindParam(':p', $p, PDO::PARAM_STR);
		$stmt->execute();
		
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$row = $stmt->rowCount();
		
		$profiles = array();
				
		foreach ($rows as $item) 
		{
			$profile = array();
			array_push($profile, $item['user_id'], $item['user_card_id'],$item['user_type'],$item['business_name'],$item['user_name']);
			array_push($profiles, $profile);
			 
		}
		
		return $profiles;
	}
	
	function get_traders($uid,$is_trader)
	{
		$account_status = 1;
		//get the consumer_rfid from the databas
		$stmt = DB::get()->prepare("SELECT * FROM tbl_users WHERE is_trader=:is_trader AND user_account_status=:account_status ORDER BY business_name ASC");
		$stmt->bindParam(':account_status', $account_status, PDO::PARAM_STR);
		$stmt->bindParam(':is_trader', $is_trader, PDO::PARAM_STR);
		$stmt->execute();	
		
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$row = $stmt->rowCount();
		
		$traders = array();
				
		foreach ($rows as $item) 
		{
			$trader = array();
			if ($item['user_id'] != $uid)
			{
				array_push($trader, $item['user_id'], $item['user_card_id'],stripslashes($item['business_name']),stripslashes($item['user_name']),$item['user_email']);
				array_push($traders, $trader);
			}
		}
		
		return $traders;
	}
	
	function get_traders_frequent_transactions($uid)
	{
		$stmt = DB::get()->prepare("SELECT DISTINCT(consumer_id),tbl_users.user_id, tbl_users.user_card_id,tbl_users.business_name,tbl_users.user_name,tbl_users.user_email FROM tbl_transactions INNER JOIN tbl_users ON tbl_transactions.`consumer_id`=tbl_users.user_card_id
		WHERE trader_id=:tid ORDER BY tbl_users.business_name ASC");
		$stmt->bindParam(':tid', $uid, PDO::PARAM_STR);
		$stmt->execute();	
		
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$row = $stmt->rowCount();
		$traders = array();

		foreach ($rows as $item) 
		{
			$trader = array();
			if ($item['user_id'] != $uid)
			{
				array_push($trader, $item['user_id'], $item['user_card_id'],stripslashes($item['business_name']),stripslashes($item['user_name']),$item['user_email']);
				array_push($traders, $trader);
			}
		}
		
		return $traders;
	}
	
	function get_all_unverified_traders()
	{
		$uas = 0;
		$ist = 1;
		
		$stmt = DB::get()->prepare("SELECT * FROM tbl_users WHERE is_trader=:ist AND user_account_status=:uas");
		$stmt->bindParam(':ist', $ist, PDO::PARAM_STR);
		$stmt->bindParam(':uas', $uas, PDO::PARAM_STR);
		$stmt->execute();	
		
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$row = $stmt->rowCount();
		$traders = array();

		foreach ($rows as $item) 
		{
			$trader = array();
			
			array_push($trader, $item['user_id'], $item['user_card_id'],stripslashes($item['business_name']),stripslashes($item['user_name']),$item['user_email'],$item['user_dob'],$item['user_postcode'],$item['timestamp'],$item['last_action']);
			array_push($traders, $trader);
			
		}
		
		return $traders;
	}
	
	function get_transaction_points()
	{
		//get the consumer_rfid from the databas
		$stmt = DB::get()->prepare(
		"SELECT trader_id, consumer_id,ut.business_name,uc.business_name,ut.user_business_lat,ut.user_business_lon,uc.user_business_lat,uc.user_business_lon
			FROM tbl_transactions AS t
			LEFT JOIN tbl_users AS ut
			ON t.trader_id=ut.user_card_id 
			LEFT JOIN tbl_users AS uc
			ON t.consumer_id=uc.user_card_id");
		$stmt->execute();
		
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		
		$row = $stmt->rowCount();
		$trans = array();
				
		foreach ($rows as $item) 
		{
			$tran = array();
			array_push($tran, $item[2], $item[4],$item[5],$item[3], $item[6],$item[7]);
			array_push($trans, $tran); 
		}
		
		return $trans;
	}
	
	function get_activity($uid, $sort, $order, $offset, $limit)
	{
		//if limit is 0 do not limit the returned data
		if ($limit == 0)
		{
			$stmt = DB::get()->prepare("SELECT * FROM tbl_transactions AS t LEFT JOIN tbl_users AS u ON t.consumer_id=u.user_card_id WHERE t.trader_id=:tid ORDER BY $sort $order");
			$stmt->bindParam(':tid', $uid, PDO::PARAM_STR);
			//$stmt->bindValue(':order', $sort, PDO::PARAM_STR);
		}
		else
		{
			$stmt = DB::get()->prepare("SELECT * FROM tbl_transactions AS t LEFT JOIN tbl_users AS u ON t.consumer_id=u.user_card_id WHERE trader_id=:tid LIMIT :offset, :limit");
			$stmt->bindParam(':tid', $uid, PDO::PARAM_STR);
			$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
		}
		$stmt->execute();		
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$row = $stmt->rowCount();
		
		$full_activity = array();
				
		foreach ($rows as $item) 
		{
			$activity = array();
			array_push($activity, $item['trans_id'],$item['consumer_id'],$item['consumer_type'],$item['consumer_name'],$item['trans_lat'],$item['trans_lon'],$item['trans_type'],$item['trans_origin'],$item['trans_price'],$item['trans_points'],$item['trans_timestamp'],$item['upload_timestamp'],$item['user_name'],$item['user_postcode'],$item['user_email'],$item['business_name'],$item['is_trader']);
			
			array_push($full_activity, $activity); 
		}
		
		return $full_activity;
	}
	
	function get_number_transactions($uid)
	{
		$stmt = DB::get()->prepare("SELECT count(trans_id) FROM tbl_transactions WHERE trader_id=:tid");
		$stmt->bindParam(':tid', $uid, PDO::PARAM_STR);
		$stmt->execute();		
		$result = $stmt->fetchColumn();
		return $result;
	}
	
	function get_total_points($uid)
	{
		$stmt = DB::get()->prepare("SELECT sum(trans_points) FROM tbl_transactions WHERE consumer_id=:tid");
		$stmt->bindParam(':tid', $uid, PDO::PARAM_STR);
		$stmt->execute();		
		$trans_pts = $stmt->fetchColumn();
		
		$stmt1 = DB::get()->prepare("SELECT sum(consumer_points_deducted) FROM tbl_redeems WHERE consumer_id=:tid");
		$stmt1->bindParam(':tid', $uid, PDO::PARAM_STR);
		$stmt1->execute();		
		$redeem_pts = $stmt1->fetchColumn();
		
		$total = $trans_pts - $redeem_pts;
		
		return $total;
	}
	
	function get_number_of_users($who)
	{
		$stmt = DB::get()->prepare("SELECT count(user_id) FROM tbl_users WHERE $who");
		$stmt->execute();		
		$number = $stmt->fetchColumn();
		return $number;
	}
	
	function convert_to_date($date)
	{
		$date = strtotime($date);
		$date = date('d-m-Y H:i:s', $date);
		return $date;
	}
	
	function convert_to_date_no_time($date)
	{
		$date = strtotime($date);
		$date = date('d-m-Y', $date);
		return $date;
	}
	
	function get_user($uid)
	{
		$stmt = DB::get()->prepare("SELECT * FROM tbl_users WHERE user_card_id=:uid LIMIT 1");
		$stmt->bindParam(':uid', $uid, PDO::PARAM_STR);
		$stmt->execute();		
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$row = $stmt->rowCount();
		
		if ($row == 1)
		{
			foreach ($rows as $item) 
			{
				$user = array();
				array_push($user, $item['user_name'],$item['user_gender'],$item['user_dob'],$item['user_postcode'],$item['user_ethical_pref'],$item['user_email'],$item['user_type'],$item['business_name'],$item['is_trader'],$item['is_manufacturer'],$item['is_wholesaler'],$item['is_retailer'],$item['is_service'],$item['is_fixed_trader'],$item['is_non_fixed_trader'],$item['user_business_lat'],$item['user_business_lon'],$item['goods_services'],$item['statement'],$item['linkedin_id'],$item['linkedin_headline'],$item['linkedin_img'],$item['linkedin_summary'],$item['linkedin_profile_url']);
			}
		}
		return $user;
	}
	
	function get_associations($tid)
	{
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
		<h3>Linked Cards</h3>
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
					$html_data .= "<tr><td>".$item['user_name']."</td><td>".$item['user_id']."</td></tr>";
				}
				else
				{
					$html_data .= "<tr><td><a href='#' target='_blank'>".$item['user_name']."</a></td><td>".$item['user_id']."</td></tr>";
				}
			}
		}	
			
		$html_data .= "</table>";	
		
		return $html_data;
	}
	
	function geoencodeaddress($string){
 
	   $string = str_replace (" ", "+", urlencode($string));
	   $details_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$string."&sensor=false";
	 
	   $ch = curl_init();
	   curl_setopt($ch, CURLOPT_URL, $details_url);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   $response = json_decode(curl_exec($ch), true);
	 
	   // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
	   if ($response['status'] != 'OK') {
		return null;
	   }
	 
	   //print_r($response);
	   $geometry = $response['results'][0]['geometry'];
	 
		$longitude = $geometry['location']['lng'];
		$latitude = $geometry['location']['lat'];
	 
		$array = array(
			'latitude' => $geometry['location']['lat'],
			'longitude' => $geometry['location']['lng'],
			'location_type' => $geometry['location_type'],
		);
	 
		return $array;
	}
	
	function get_bartercard_businesses()
	{
		//get the consumer_rfid from the databas
		$stmt = DB::get()->prepare("SELECT * FROM tbl_barter_businesses ORDER BY RAND()");
		$stmt->execute();
			
		//setting the fetch mode  
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->bindColumn('b_name', $bname);
		$stmt->bindColumn('b_url', $burl);
		$stmt->bindColumn('b_type', $btype);
		$stmt->bindColumn('b_contact_number', $bnumber);
		$stmt->bindColumn('b_contact_person', $bperson);
		$stmt->fetch(PDO::FETCH_BOUND);
		$row = $stmt->rowCount();
			
		$businesses = array();
		if ($row != 0)
		{
			foreach ($rows as $item) 
			{
				$business = array();
				array_push($business, $item['b_name'],$item['b_url'],$item['b_type'],$item['b_contact_number'],$item['b_contact_person']);
				array_push($businesses,$business);
			}
		}	
		return $businesses;
	}
	
	function get_project_updates()
	{
		//get the consumer_rfid from the databas
		$stmt = DB::get()->prepare("SELECT * FROM tbl_project_updates");
		$stmt->execute();
			
		//setting the fetch mode  
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->bindColumn('update_title', $title);
		$stmt->bindColumn('update_desc', $desc);
		$stmt->bindColumn('update_timestamp', $time);
		$stmt->fetch(PDO::FETCH_BOUND);
		$row = $stmt->rowCount();
			
		$updates = array();
		if ($row != 0)
		{
			foreach ($rows as $item) 
			{
				$update = array();
				array_push($update, $item['update_title'],$item['update_desc'],$item['update_timestamp']);
				array_push($updates,$update);
			}
		}	
		return $updates;
	}
	
	function get_snapshot_data($tid)
	{
		//get the consumer_rfid from the databas
		$stmt = DB::get()->prepare("SELECT * FROM tbl_trader_snapshots WHERE trader_card_id=:tid ORDER BY p_id DESC LIMIT 1");
		$stmt->bindParam(':tid', $tid, PDO::PARAM_STR);
		$stmt->execute();

		$stmt->bindColumn('trader_local_spend', $tls);
		$stmt->bindColumn('trader_non_local_spend', $tnls);
		$stmt->bindColumn('timestamp', $t);
		$stmt->fetch(PDO::FETCH_BOUND);
		$row = $stmt->rowCount();
		
		if ($row == 0)	
		{
			$tls = 0;
			$tnls = 0;
			$timestamp = 0;
		}
		
		$data = array(
		'local_spend' => $tls,
		'non_local_spend' => $tnls,
		'timestamp' => $t
		);
	 
		
		return $data;
	}
	
	function check_snapshot_timestamp($tid)
	{
		//get the consumer_rfid from the databas
		$stmt = DB::get()->prepare("SELECT * FROM tbl_trader_snapshots WHERE trader_card_id=:tid ORDER BY p_id DESC LIMIT 1");
		$stmt->bindParam(':tid', $tid, PDO::PARAM_STR);
		$stmt->execute();

		$stmt->bindColumn('timestamp', $t);
		$stmt->fetch(PDO::FETCH_BOUND);
		$row = $stmt->rowCount();
		
		$status = false;
		
		if ($row == 0)
		{	
			$timestamp = 0;
			$status = true;
		}
		else
		{
			if( strtotime($t) > strtotime('-7 day') )
			{
				$status = false;
			}
			else
			{
				$status = true;	
			}
		}
		
		$data_to_send = array('response' => "OK", 'timestamp' => $t, 'checkStatus'=>$status);
		echo json_encode($data_to_send);
	}
	
	
	function get_community_spend($tid)
	{
		
		$b = "barter";
		$l = "local_non_barter";
		$e = "external";
		
			
		$stmt = DB::get()->prepare("SELECT COUNT( trans_id ) AS total_trans,
		(SELECT SUM(trans_price) FROM tbl_transactions WHERE consumer_type=:barter) AS barter_community_spend, 
		(SELECT SUM(trans_price) FROM tbl_transactions WHERE consumer_type=:local) AS local_community_spend, 
		(SELECT SUM(trans_price) FROM tbl_transactions WHERE consumer_type=:non_local) AS non_local_community_spend, 
		(SELECT SUM(trans_price) FROM `tbl_transactions` WHERE trader_id=:tid AND consumer_type=:barter) AS my_barter ,
		(SELECT SUM(trans_price) FROM `tbl_transactions` WHERE trader_id=:tid AND consumer_type=:local) AS my_local, 
		(SELECT SUM(trans_price) FROM `tbl_transactions` WHERE trader_id=:tid AND consumer_type=:non_local) AS my_non_local 
		FROM tbl_transactions");
		$stmt->bindParam(':tid', $tid, PDO::PARAM_STR);
		$stmt->bindParam(':barter', $b, PDO::PARAM_STR);
		$stmt->bindParam(':local', $l, PDO::PARAM_STR);
		$stmt->bindParam(':non_local', $e, PDO::PARAM_STR);
		$stmt->execute();
		
		//setting the fetch mode  
		$stmt->setFetchMode(PDO::FETCH_ASSOC); 
		$row = $stmt->rowCount();
	
		$stmt->bindColumn('barter_community_spend', $barter);
		$stmt->bindColumn('local_community_spend', $local);
		$stmt->bindColumn('non_local_community_spend', $non_local);
		$stmt->bindColumn('my_barter', $my_barter);
		$stmt->bindColumn('my_local', $my_local);
		$stmt->bindColumn('my_non_local', $my_non_local);
		$stmt->fetch(PDO::FETCH_BOUND);
		
		if($barter==NULL)$barter=0;
		if($non_local==NULL)$non_local=0;
		if($local==NULL)$local=0;
		if($my_barter==NULL)$my_barter=0;
		if($my_local==NULL)$my_local=0;
		if($my_non_local==NULL)$my_non_local=0;
		
		if($barter==0)
			$barter_total=0;
		else
			$barter_total = ($my_barter/$barter)*100;
		
		if($local==0)
			$local_total=0;
		else
			$local_total = ($my_local/$local)*100;
			
		if($non_local==0)
			$non_local_total=0;
		else
			$non_local_total = ($my_non_local/$non_local)*100;

		$ajax_message = true;
		$ajax_response = $row."";
		
		$data_to_send = array('response' => $ajax_response, 'message' => $ajax_message, 
		'barter_community_spend' => $barter, 'local_community_spend'=>$local, 'non_local_community_spend'=>$non_local, 'my_barter' =>$my_barter, 'my_local' =>$my_local, 'my_non_local' =>$my_non_local, 'barter_total'=>$barter_total, 'local_total'=>$local_total,'non_local_total'=>$non_local_total);
		echo json_encode($data_to_send);
	 
	}
	
	function get_system_spend_by_month($tid)
	{
		//get the data for the current year
		$year = date("Y");
	
		/*$stmt = DB::get()->prepare("
		SELECT YEAR(`trans_timestamp`) as year,MONTH(`trans_timestamp`) as month, 
		(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_barter AND trader_id =:tid AND MONTH(  `trans_timestamp` ) = month) as my_barter_total,
		(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_barter AND MONTH(`trans_timestamp`) = month) as barter_total,
		(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_local AND MONTH(`trans_timestamp`) = month)  as local_total ,
		(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_non_local AND MONTH(  `trans_timestamp` ) = month) as non_local_total
		FROM tbl_transactions WHERE YEAR(`trans_timestamp`)  =:year 
		GROUP BY MONTH(  `trans_timestamp` )
		");*/
		
		$stmt = DB::get()->prepare("SELECT tbl_months.m_id AS month, 
(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_barter AND trader_id =:tid AND MONTH(  `trans_timestamp` ) = month AND  YEAR(`trans_timestamp`)  =:year ) as my_barter_total,
(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_barter AND MONTH(`trans_timestamp`) = month AND  YEAR(`trans_timestamp`)  =:year ) as barter_total,
(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_local AND MONTH(`trans_timestamp`) = month AND  YEAR(`trans_timestamp`)  =:year )  as local_total, 
(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_non_local AND MONTH(  `trans_timestamp` ) = month AND  YEAR(`trans_timestamp`)  =:year ) as non_local_total,
(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  MONTH(  `trans_timestamp` ) = month AND  YEAR(`trans_timestamp`)  =:year ) as total_wealth
FROM tbl_months");
		
		$b = 'barter';
		$l = 'local_non_barter';
		$e = 'external';
		
		$stmt->bindParam(':tid', $tid, PDO::PARAM_STR);
		$stmt->bindParam(':type_barter', $b, PDO::PARAM_STR);
		$stmt->bindParam(':type_local', $l, PDO::PARAM_STR);
		$stmt->bindParam(':type_non_local', $e, PDO::PARAM_STR);
		$stmt->bindParam(':year', $year, PDO::PARAM_STR);
		$stmt->execute();
		
		//setting the fetch mode  
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
		$row = $stmt->rowCount();
		
		$mbtotals = array();	
		$btotals = array();
		$ltotals = array();
		$etotals = array();
		$wtotals = array();
				
		if ($row != 0)
		{
			for ($h=0;$h<12;$h++)
			{
				$total0 = array();
				array_push($total0, $rows[$h]['month'], $rows[$h]['my_barter_total']);
				array_push($mbtotals, $total0);
			}
			
			for ($h=0;$h<12;$h++)
			{
				$total0 = array();
				array_push($total0, $rows[$h]['month'], $rows[$h]['barter_total']);
				array_push($btotals, $total0);
			}
			
			for ($h=0;$h<12;$h++)
			{
				$total0 = array();
				array_push($total0, $rows[$h]['month'], $rows[$h]['local_total']);
				array_push($ltotals, $total0);
			}
			
			for ($h=0;$h<12;$h++)
			{
				$total0 = array();
				array_push($total0, $rows[$h]['month'], $rows[$h]['non_local_total']);
				array_push($etotals, $total0);
			}
			for ($h=0;$h<12;$h++)
			{
				$total0 = array();
				array_push($total0, $rows[$h]['month'], $rows[$h]['total_wealth']);
				array_push($wtotals, $total0);
			}
		}	
		$data = get_average_non_barter_spends($year);
	
		$data_to_send = array('response' => "OK", 'my_barter_output' => $mbtotals,'barter_output' => $btotals, 'local_output' => $ltotals, 'non_local_output' => $etotals, 'total_wealth' => $wtotals, 'average_local_spend' => $data[0], 'average_non_local_spend' => $data[1]);
		echo json_encode($data_to_send);
	}
	
	function get_average_non_barter_spends($year)
	{
		$stmt = DB::get()->prepare("SELECT AVG(`trader_local_spend`) AS local_spend, AVG(`trader_non_local_spend`) AS non_local_spend FROM `tbl_trader_snapshots` WHERE YEAR(`timestamp`) =:y");
		$stmt->bindParam(':y', $year, PDO::PARAM_STR); 
		$stmt->execute();
		
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$row = $stmt->rowCount();
		$data = array();
	
		if ($row != 0)
		{
			array_push($data, $rows[0]['local_spend'], $rows[0]['non_local_spend']);
		}
		else
		{
			array_push($data, '0', '0');
		}
		
		return $data;
	}
	
	
	function get_overview_data($tid)
	{
		$stmt = DB::get()->prepare("
		SELECT 
		(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_barter AND trader_id =:tid) as my_barter_total,
		(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_barter) as barter_total,
		(SELECT IFNULL(COUNT(`trans_id`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_barter) as barter_count,
		(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_local AND trader_id =:tid) as my_local_total,
		(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions` WHERE  `consumer_type` =:type_non_local AND trader_id =:tid) as my_non_local_total,
		(SELECT IFNULL(SUM(`trans_price`),0) FROM  `tbl_transactions`) as total_wealth
		FROM tbl_transactions
		");
		
		$b = 'barter';
		$l = 'local_non_barter';
		$e = 'external';
		
		$stmt->bindParam(':tid', $tid, PDO::PARAM_STR);
		$stmt->bindParam(':type_barter', $b, PDO::PARAM_STR);
		$stmt->bindParam(':type_local', $l, PDO::PARAM_STR);
		$stmt->bindParam(':type_non_local', $e, PDO::PARAM_STR);
		$stmt->execute();
		
		$stmt->bindColumn('my_barter_total', $my_trades);
		$stmt->bindColumn('barter_total', $trades);
		$stmt->bindColumn('barter_count', $number_of_trans);
		$stmt->bindColumn('my_local_total', $my_local_total);
		$stmt->bindColumn('my_non_local_total', $my_non_local_total);
		$stmt->bindColumn('total_wealth', $total_wealth);
		$stmt->fetch(PDO::FETCH_BOUND);
		$row = $stmt->rowCount();
		
		if ($row == 0)	
		{
			$my_trades = 0;
			$trades = 0;
			$number_of_trans = 0;
			$my_local_total = 0;
			$my_non_local_total = 0;
			$total_wealth = 0;
		}
		
		$data = array(
		'my_trades' => $my_trades,
		'trades' => $trades,
		'count' => $number_of_trans,
		'my_local_total' => $my_local_total,
		'my_non_local_total' => $my_non_local_total,
		'total_wealth' => $total_wealth
		);
	 
		
		return $data;
	}


?>

