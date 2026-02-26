<?php
declare(strict_types = 0);

namespace SaQle\Orm\Database\Config;

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

	public static function from_connection(string $connection){
		$db_config = config('db.connections')[$connection];
		return new static(
			 driver: $db_config['driver'], 
		     database: $db_config['database'], 
		     port: $db_config['port'], 
		     username: $db_config['username'], 
		     password: $db_config['password'], 
		     host: $db_config['host'] ?? 'localhost', 
		     prefix: $db_config['prefix'] ?? '', 
		     charset: $db_config['charset'] ?? 'utf8', 
		     collation: $db_config['collation'] ?? 'utf8_general_ci',
	         options: $db_config['options'] ?? []
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
