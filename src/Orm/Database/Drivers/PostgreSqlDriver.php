<?php
namespace SaQle\Orm\Database\Drivers;

class PostgreSqlDriver extends DbDriver {
	 public function create_database(...$db_configurations){
		 return true;
	 }
}

