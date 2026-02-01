<?php
namespace SaQle\Orm\Database\Manager;

use SaQle\Orm\Database\Manager\Base\DbManager;

class DbManagerFactory {

	 const MYSQL = 'mysql';
	 const POSTGRESS = 'pgsql';

	 private $_manager;

	 public function __construct(string $db_type){
	 	 $contextparams = config('db_context_classes')[$dbclass];
		 switch($contextparams['type']->value){
			 case self::MYSQL:
			     $this->_manager = new MySQLDbManager($contextparams);
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

