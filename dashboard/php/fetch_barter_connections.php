<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once("fetch_barter_profile.php");

function fetch_barter_connections($t)
{
		$obj = fetch_linkedin_connections($t);
		$linkedin_connections = array();

		foreach ($obj->values as $item) 
		{
			$connection = array();
			array_push($linkedin_connections, $item->id); 
		}
		$account_status = 1;
		$is_trader = 1;
			
		$placeholders = rtrim(str_repeat('?, ', count($linkedin_connections)), ', ') ;
		$stmt = DB::get()->prepare("SELECT * FROM tbl_users WHERE linkedin_id IN ($placeholders) ");
		$stmt->execute($linkedin_connections);
	
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$row = $stmt->rowCount();
		
		$barter_connections = array();
		
		if ($row != 0)
		{
			foreach ($rows as $item) 
			{
				if ($item['is_trader'] == 1 && $item['user_account_status'] == 1)
				{
					$barter_connection = array();
					array_push($barter_connection, $item['user_id'], $item['user_card_id'],$item['business_name'],$item['user_name'],$item['user_email']);
					array_push($barter_connections, $barter_connection);
				}
				
			}
		}	
		
		return $barter_connections;	
} 
?>