<?php
declare(strict_types = 0);
namespace SaQle\Orm\Database\Attributes;

use SaQle\Orm\Database\DbTypes;
use SaQle\Orm\Database\DbPorts;

#[Attribute(Attribute::TARGET_CLASS)]
class DbContextOptions extends IDbContextOptions{
	public function __construct(
		 private DbTypes $type, 
		 private string  $name, 
		 private DbPorts $port, 
		 private string  $username = '', 
		 private string  $password = '', 
		 private string  $host = 'localhost', 
		 private string  $prefix = '', 
		 private string  $charset = 'utf8', 
		 private string  $collation = 'utf8_general_ci')
	{}

	public function get_name(){
		return $this->name;
	}
	public function get_type(){
		return $this->type;
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
?>