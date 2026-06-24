<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database;

use SaQle\Orm\Entities\Model\Interfaces\{IThroughModel, ITempModel};
use SaQle\Core\Migration\Models\Migration;
use SaQle\Session\Models\Session;
use SaQle\Orm\Entities\Model\TempId;
use SaQle\Core\Queue\Models\{FailedJob, Job, JobBatch};
use RuntimeException;

abstract class Schema {

	 //all the models registered in schema
	 protected array $models = [];

	 public function get_defined_models() : array {
	 	 $resolved = [];
         foreach($this->models as $key => $model_class){
             $table = is_numeric($key) ? $this->infer_table_name($model_class) : $key;
             $resolved[$table] = $model_class;
         }

         return $resolved;
	 }

	 public function get_models() : array {
         return array_merge(
         	 $this->get_defined_models(),
         	 ['model_temp_ids' => TempId::class]
         );
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

	 public function get_temporary_models() : array {
	 	 $models = [];
	 	 foreach($this->get_models() as $tablename => $modelclass){
	 	 	 $interfaces = class_implements($modelclass);
	 	 	 if(in_array(ITempModel::class, $interfaces)){
	 	 	     $models[$tablename] = $modelclass;
	 	     }
	 	 }

	 	 return $models;
	 }

	 public function get_through_models() : array {
	 	 $models = [];
	 	 foreach($this->get_models() as $tablename => $modelclass){
	 	 	 $interfaces = class_implements($modelclass);
 	 	 	 if(in_array(IThroughModel::class, $interfaces)){
	 	 	     $models[$tablename] = $modelclass;
	 	     }
	 	 }

	 	 return $models;
	 }

	 protected function infer_table_name(string $model_class): string {
         $basename = basename(str_replace('\\', '/', $model_class));
         return str_plural(snake_case($basename));
     }

     public function get_table_for_model(string $model_class) : string {
         
         $models = $this->get_models();
         $model_classes = array_values($models);
         $index = array_search($model_class, $model_classes, true);
         
         if($index === false){
             throw new RuntimeException($model_class. ": Not registered in '{".static::class."}' schema.");
         }

         return array_keys($models)[$index];
     }

     public function get_model_for_table(string $table_name) : string {
         
         $models = $this->get_models();
     	 $table_names = array_keys($models);
         $index = array_search($table_name, $table_names, true);
         
         if($index === false){
             throw new RuntimeException($table_name. ": Not registered in '{".static::class."}' schema.");
         }

         return array_values($models)[$index];
     }
}
