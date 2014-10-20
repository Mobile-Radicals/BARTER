<?php

	class DB{
		private static $instance = null;
		
		public static function get(){
			if(self::$instance == null){
				try{
					self::$instance = new PDO('mysql:host=localhost;dbname=DB HERE', 'DB USERNAME HERE', 'PASSWORD_HERE');
				}
				catch(PDOException $e){
					throw $e;
				}
			}
			return self::$instance;
		}
	}

?>