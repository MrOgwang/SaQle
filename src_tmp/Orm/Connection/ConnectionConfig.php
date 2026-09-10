<?php
declare(strict_types = 0);

namespace SaQle\Orm\Connection;

class ConnectionConfig {
	 public function __construct(
		 private string  $driver, 
		 private string  $database, 
		 private int     $port, 
		 private string  $username = '', 
		 private string  $password = '', 
		 private string  $host = 'localhost', 
		 private string  $prefix = '', 
		 private string  $charset = 'utf8', 
		 private string  $collation = 'utf8_general_ci',
	     private array   $options = []
	 ){}

	 public function get_database(){
		 return $this->database;
	 }

	 public function get_driver(){
		 return $this->driver;
	 }

	 public function get_charset(){
		 return $this->charset;
	 }

	 public function get_collation(){
		 return $this->collation;
	 }

	 public function get_prefix(){
		 return $this->prefix;
	 }

	 public function get_username(){
		 return $this->username;
	 }

	 public function get_password(){
		 return $this->password;
	 }

	 public function get_host(){
		 return $this->host;
	 }

	 public function get_port(){
		 return $this->port;
	 }

	 public function get_options(){
		 return $this->options;
	 }

	 public static function from_connection(string $connection_key, bool $with_database = true){

	 	 $conn_parts = explode(".", $connection_key);
	 	 $conn_name  = $conn_parts[0];

	 	 $conn_config = config('db.connections')[$conn_name];

		 return new static(
			 driver: $conn_config['driver'], 
		     database: $with_database ? ConnectionTarget::make($connection_key) : '', 
		     port: $conn_config['port'], 
		     username: $conn_config['username'], 
		     password: $conn_config['password'], 
		     host: $conn_config['host'] ?? 'localhost', 
		     prefix: $conn_config['prefix'] ?? '', 
		     charset: $conn_config['charset'] ?? 'utf8', 
		     collation: $conn_config['collation'] ?? 'utf8_general_ci',
	         options: $conn_config['options'] ?? []
		 );
	 }

	 public function to_array(){
		 return [
			 'driver' => $this->driver,
		     'database' => $this->database, 
		     'port' => $this->port, 
		     'username' => $this->username, 
		     'password' => $this->password, 
		     'host' => $this->host, 
		     'prefix' => $this->prefix, 
		     'charset' => $this->charset, 
		     'collation' => $this->collation,
	         'options' => $this->options
		 ];
	 }
}
