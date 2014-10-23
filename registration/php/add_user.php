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

if (isset($_REQUEST['card_id']))
{
	//get the data
	$card_id = mysql_escape_string($_REQUEST['card_id']);
	$account = mysql_escape_string($_REQUEST['account']);
	$account_name = mysql_escape_string($_REQUEST['account_name']);
	//$username = mysql_escape_string($_POST['username']);
	//$password1 = mysql_escape_string($_POST['password1']);
	$password1 = "";
	$dob = mysql_escape_string($_REQUEST['dob']);
	
	$phpdate = strtotime( $dob );
	$dob = date( 'Y-m-d', $phpdate );
	
	$email = mysql_escape_string($_REQUEST['email']);
	$gender = mysql_escape_string($_REQUEST['gender']);
	$postcode = mysql_escape_string($_REQUEST['postcode']);
	//$postcode2 = mysql_escape_string($_POST['postcode1']);
	
	//$postcode = $postcode1." ".$postcode2;
	
	
	
	//taken the movie tag out
	//$movie = mysql_escape_string($_POST['movie']);
	$movie ="";
	$ethical_pref = mysql_escape_string($_REQUEST['ethical_pref_type']);
	
	
	if (($account == 'business') || ($account == 'organisation'))
	{
		$user_type = $account;
		$account = 1;
		$wholesaler = mysql_escape_string($_REQUEST['wholesaler']);
		$service = mysql_escape_string($_REQUEST['service']);
		$manufacturer = mysql_escape_string($_REQUEST['manufacturer']);
		$retailer = mysql_escape_string($_REQUEST['retailer']);
		$fixed = mysql_escape_string($_REQUEST['fixed']);
		$normadic = mysql_escape_string($_REQUEST['normadic']);
		$goods_services = mysql_escape_string($_REQUEST['goods_services']);
		$trader_statement = mysql_escape_string($_REQUEST['statement']);
		$business_name = mysql_escape_string($_REQUEST['account_business_name']);
		
		$bpostcode = mysql_escape_string($_REQUEST['b_postcode']);
		//$bpostcode2 = mysql_escape_string($_POST['b_postcode1']);
		
		//$bpostcode = $bpostcode1." ".$bpostcode2;
		
		//convert postcode data into lat/lon
		$latlon = geoencodeaddress($bpostcode);
		
		//print_r($latlon);
		$barter_card = "unknown";
		$emplyment_status = "";
	}
	
	else
	{
		$user_type = $account;
		$account = 0;
		$service = 0;
		$wholesaler = 0;
		$manufacturer = 0;
		$retailer = 0;
		$fixed = 0;
		$normadic = 0;
		$goods_services = "";
		$trader_statement = "";
		$business_name = "";
		$bpostcode = "";
		$latlon = geoencodeaddress($postcode);
		$barter_card = mysql_escape_string($_REQUEST['barter_card']);
		$emplyment_status = mysql_escape_string($_REQUEST['employment_status']);
	}
	
	/*$environmental = mysql_escape_string($_POST['environmental']);
	$social = mysql_escape_string($_POST['social']);
	$economic = mysql_escape_string($_POST['economic']);
	$wellbeing = mysql_escape_string($_POST['wellbeing']);*/
	
	//$ethical_pref = $environmental.";".$social.";".$economic.";".$wellbeing;
	
	//get the consumer_rfid from the databas
	$stmt = DB::get()->prepare("SELECT user_card_id FROM tbl_users WHERE user_card_id=:card_id");
	$stmt->bindParam(':card_id', $card_id, PDO::PARAM_STR);
	$stmt->execute();
		
	//setting the fetch mode  
	$stmt->setFetchMode(PDO::FETCH_ASSOC); 
	$row = $stmt->rowCount();
	
	$pass_key_to_send = generate_password();
	$pass_key = salt_password($pass_key_to_send);
	
	//set the response for the client	
	$ajax_response = false;
		if($row == 0)
		{
			//no consumers with this card are registerd
			//add new consumer
			$statement = DB::get()->prepare("INSERT INTO tbl_users (user_name, user_card_id, user_gender, user_dob, user_postcode, user_ethical_pref, user_email, user_type, user_character, user_employment_status, hear_about_us, business_name, business_postcode, is_trader, is_manufacturer, is_wholesaler, is_retailer, is_service, is_fixed_trader, is_non_fixed_trader, user_business_lat, user_business_lon, goods_services, statement, pass_key, user_pass) values
			(:uname, :rfid, :gender, :dob, :postcode, :ethical_pref, :email, :type, :movie, :estatus, :hear, :bname, :bpc, :trader, :manufacturer, :wholesaler, :retailer, :service, :fixed_trader, :non_fixed_trader, :lat, :lon, :items, :trader_statement, :passkey, :upass)");
			$statement->bindParam(':uname', $account_name, PDO::PARAM_STR);
			$statement->bindParam(':rfid', $card_id, PDO::PARAM_STR);
			$statement->bindParam(':gender', $gender, PDO::PARAM_STR);
			$statement->bindParam(':dob', $dob, PDO::PARAM_STR);
			$statement->bindParam(':postcode', $postcode, PDO::PARAM_STR);
			$statement->bindParam(':ethical_pref', $ethical_pref, PDO::PARAM_STR);
			$statement->bindParam(':email', $email, PDO::PARAM_STR);
			$statement->bindParam(':type', $user_type, PDO::PARAM_STR);
			$statement->bindParam(':movie', $movie, PDO::PARAM_STR);
			$statement->bindParam(':estatus', $emplyment_status, PDO::PARAM_STR);
			$statement->bindParam(':hear', $barter_card, PDO::PARAM_STR);
			$statement->bindParam(':bname', $business_name, PDO::PARAM_STR);
			$statement->bindParam(':bpc', $bpostcode, PDO::PARAM_STR);
			$statement->bindParam(':trader', $account, PDO::PARAM_INT);
			$statement->bindParam(':manufacturer', $manufacturer, PDO::PARAM_INT);
			$statement->bindParam(':wholesaler', $wholesaler, PDO::PARAM_INT);
			$statement->bindParam(':retailer', $retailer, PDO::PARAM_INT);
			$statement->bindParam(':service', $service, PDO::PARAM_INT);
			$statement->bindParam(':fixed_trader', $fixed, PDO::PARAM_INT);
			$statement->bindParam(':non_fixed_trader', $normadic, PDO::PARAM_INT);
			$statement->bindParam(':lat', $latlon['latitude'], PDO::PARAM_STR);
			$statement->bindParam(':lon', $latlon['longitude'], PDO::PARAM_STR);
			$statement->bindParam(':items', $goods_services, PDO::PARAM_STR);
			$statement->bindParam(':trader_statement', $trader_statement, PDO::PARAM_STR);
			$statement->bindParam(':passkey', $pass_key, PDO::PARAM_STR);
			$statement->bindParam(':upass', $password1, PDO::PARAM_STR);
			
			//$statement->debugDumpParams();
			
			//execute the query
			if($statement->execute())
			{	
				sendEmail($account_name, $email, $pass_key, $user_type,'Welcome to BARTER',$card_id, $business_name);
				$ajax_response = true;
				$ajax_message = "Welcome! $account_name, you have successfully been registered. We will shortly send you an email, you will need follow the instructions in order to verify your account with us.";
			}
			else
			{
				$ajax_response = false;
				$ajax_message = "There seems to be an issue with our system at the moment, please try again later or alternativly contact us on info@barterproject.org";
			}
		}
		else
		{
			$ajax_message = "Someone with that card number has already been registered, please make sure the number printed on the front of your card is correct.";
		}
}
else
{
	$ajax_response = "";
	$ajax_message = "";
}
	$data_to_send = array('response' => $ajax_response, 'message' => $ajax_message);
	echo json_encode($data_to_send);
	
	

?>