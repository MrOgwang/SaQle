<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database;

use SaQle\Orm\Entities\Model\Interfaces\{IThroughModel, ITempModel};

abstract class Schema {

	 //all the models regsieted in schema
	 protected array $models = [];

	 public function get_models() : array {
	 	 $resolved = [];
         foreach($this->models as $key => $model_class){
             $table = is_numeric($key) ? $this->get_table_for_model($model_class) : $key;
             $resolved[$table] = $model_class;
         }
         return $resolved;
	 }

	 public function get_permanent_models() : array {
	 	 $models = [];
	 	 foreach($this->get_models() as $tablename => $modelclass){
	 	 	 $interfaces = class_implements($modelclass);
	 	 	 if(!in_array(ITempModel::class, $interfaces)){
	 	 	     $models[$tablename] = $modelclass;
	 	     }
	 	 }

	 	 return $models;
	 }

	 protected function get_table_for_model(string $model_class): string {
         $basename = basename(str_replace('\\', '/', $model_class));
         return str_plural(snake_case($basename));
     }
}
