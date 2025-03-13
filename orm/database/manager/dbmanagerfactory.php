<?php
namespace SaQle\Orm\Database\Manager;

use SaQle\Orm\Database\Manager\Base\DbManager;
use SaQle\Orm\Database\DbTypes;
use SaQle\Orm\Database\DbPorts;

class DbManagerFactory{
	 const MYSQL = 'mysql';
	 const POSTGRESS = 'pgsql';
	 private $_manager;
	 public function __construct(...$params){
		 switch($params['type']->value){
			 case self::MYSQL:
			     $this->_manager = new MySQLDbManager(...$params);
			 break;
			 case self::POSTGRESS:
			     $this->_manager = new PostgressSQLDbManager();
			 break;
		 }
	 }

	 public function manager(){
	 	return $this->_manager;
	 }
}

?>