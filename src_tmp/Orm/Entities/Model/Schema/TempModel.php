<?php
namespace SaQle\Orm\Entities\Model\Schema;

use SaQle\Orm\Entities\Model\Interfaces\ITempModel;
use SaQle\Orm\Entities\Model\Schema\{Model, Table};
use SaQle\Core\Support\Db;

abstract class TempModel extends Model implements ITempModel{

	 abstract protected function table_schema(Table $table): void;

	 private static function get_db_driver(?string $connection = null){
	 	 
	 	 $model = get_called_class()::make();

	 	 $connection = $model::get_connection($connection);
	 	 $table = $model->get_table_name();
	 	 
	 	 $dbdriver = Db::using($connection)->driver();
 	 	 $dbdriver->connect_with_database();

 	 	 return [$dbdriver, $table];
	 }

	 public static function drop_table(?string $connection = null) : bool {
	 	 [$dbdriver, $table] = self::get_db_driver($connection);
 	 	 return $dbdriver->drop_table($table);
	 }

	 public static function create_table(?string $connection = null) : bool {
	 	 [$dbdriver, $table] = self::get_db_driver($connection);
 	 	 return $dbdriver->create_table_from_model($table, get_called_class(), true);
	 }
}
