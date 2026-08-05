<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database;

use SaQle\Orm\Entities\Model\Interfaces\{
	 IThroughModel, 
	 ITempModel
};
use SaQle\Orm\Entities\Model\TempId;
use RuntimeException;

abstract class Schema {

	 //all the models registered in schema
	 protected array $models = [];

	 public function get_defined_models() : array {

	 	 $resolved = [];

         foreach($this->models as $model_class){
         	 $model = $model_class::make();
             $resolved[$model->get_table_name()] = $model_class;
         }

         return $resolved;
	 }

	 public function get_models() : array { 

	 	 $temp_model = TempId::make();

         return array_merge(
         	 $this->get_defined_models(),
         	 [
         	 	 $temp_model->get_table_name() => TempId::class
         	 ]
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

     public function model_exists(string $model_class){
     	 $models = $this->get_models();
         $model_classes = array_values($models);
         return array_search($model_class, $model_classes, true);
     }
}
