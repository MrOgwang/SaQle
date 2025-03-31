<?php
namespace SaQle\Orm\Entities\Model\Schema;

use SaQle\Orm\Entities\Model\Interfaces\ITempModel;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};
use SaQle\Orm\Database\Manager\DbManagerFactory;

abstract class TempModel extends Model implements ITempModel{

	 abstract protected function model_setup(TableInfo $meta): void;

	 public static function drop(?string $dbclass = null) : bool {
	 	 [$dbclass, $table] = get_called_class()::get_table_n_dbcontext($dbclass);

	 	 if(!$table || !$dbclass)
	 	 	 throw new \Exception('Cannot drop temporary table! Model not registsred with any databases contexts.');

	 	 $dbmanager = (new DbManagerFactory(dbclass: $dbclass))->manager();
 	 	 $dbmanager->connect();
 	 	 return $dbmanager->drop_table($table);
	 }

	 public static function create(?string $dbclass = null) : bool {
	 	 $modelclass = get_called_class();
	 	 [$dbclass, $table] = $modelclass::get_table_n_dbcontext($dbclass);

	 	 if(!$table || !$dbclass)
	 	 	 throw new \Exception('Cannot create temporary table! Model not registsred with any databases contexts.');

	 	 $dbmanager = (new DbManagerFactory(dbclass: $dbclass))->manager();
 	 	 $dbmanager->connect();
 	 	 return $dbmanager->create_table($table, $modelclass, true);
	 }
}
?>