<?php
namespace SaQle\Orm\Entities\Model\Schema;

use SaQle\Orm\Entities\Model\Interfaces\ITempModel;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};
use SaQle\Orm\Database\Manager\DbManagerFactory;

abstract class TempModel extends Model implements ITempModel{

	 abstract protected function model_setup(TableInfo $meta): void;

	 private function get_db_manager(?string $connection = null){
	 	 [$connection, $table] = get_called_class()::get_table_and_connection($connection);
	 	 $dbmanager = (new DbManagerFactory(connection: $connection))->manager();
 	 	 $dbmanager->connect();

 	 	 return [$dbmanager, $table];
	 }

	 public static function drop(?string $connection = null) : bool {
	 	 [$dbmanager, $table] = $this->get_db_manager($connection);
 	 	 return $dbmanager->drop_table($table);
	 }

	 public static function create(?string $dbclass = null) : bool {
	 	 [$dbmanager, $table] = $this->get_db_manager($connection);
 	 	 return $dbmanager->create_table($table, $modelclass, true);
	 }
}
