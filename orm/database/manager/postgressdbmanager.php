<?php
namespace SaQle\Orm\Database\Manager;

use SaQle\Orm\Database\Manager\Base\DbManager;

class PostgressDbManager extends DbManager{
	 public function create_database(...$db_configurations){
		 return true;
	 }
}

