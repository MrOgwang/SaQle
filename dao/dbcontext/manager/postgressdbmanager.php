<?php
namespace SaQle\Dao\DbContext\Manager;

use SaQle\Dao\DbContext\Manager\Base\DbManager;

class PostgressDbManager extends DbManager{
	 public function create_database(...$db_configurations){
		 return true;
	 }
}

?>