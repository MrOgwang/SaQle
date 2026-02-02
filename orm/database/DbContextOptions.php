<?php
declare(strict_types = 0);

namespace SaQle\Orm\Database;

class DbContextOptions extends IDbContextOptions{
	public function __construct(
		 private string  $driver, 
		 private string  $database, 
		 private int     $port, 
		 private string  $username = '', 
		 private string  $password = '', 
		 private string  $host = 'localhost', 
		 private string  $prefix = '', 
		 private string  $charset = 'utf8', 
		 private string  $collation = 'utf8_general_ci')
	{}

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
}
