<?php
namespace SaQle\Orm\Connection;

use SaQle\Orm\Database\Config\ConnectionConfig;
use Exception;

ob_start();

class Connection {
	 private static $connection = null;
	 private static $last_connection_string = "";

	 protected function __construct(){}
	 protected function __clone(){}
     public function __wakeup(){
         throw new Exception("Cannot unserialize db connection!");
     }

     //Create a new database connection instance
     public static function make(ConnectionConfig $config){
     	 $connection_string = self::get_connection_string($config);
     	 if($connection_string !== self::$last_connection_string){
     	 	 $pdo = self::connect($connection_string, $config->get_username(), $config->get_password());
     	     self::$connection = $pdo;
     	     self::$last_connection_string = $connection_string;
     	 }
     	 return self::$connection;
     }

     //Construct a connection string from the database context options
	 private static function get_connection_string(ConnectionConfig $config){
		 return $config->get_driver().":host=".$config->get_host().";port=".$config->get_port().";dbname=".$config->get_database().";";
	 }

	 //Create the pdo connection object
	 private static function connect(string $connection_string, string $username, string $password){
	 	try{
			 $pdo = new \PDO($connection_string, $username, $password);
			 $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			 return $pdo;
		 }catch(\Exception $ex){
			 throw $ex;
		 }
	 }
}
