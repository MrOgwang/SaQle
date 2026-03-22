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

	 //all the models regsieted in schema
	 protected array $models = [];

	 public function get_models() : array {
	 	 $resolved = [];
         foreach($this->models as $key => $model_class){
             $table = is_numeric($key) ? $this->infer_table_name($model_class) : $key;
             $resolved[$table] = $model_class;
         }
         return array_merge(
         	 $resolved, 
         	 ['model_temp_ids' => TempId::class], 
         	 $this->get_framework_models()
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

     public function get_table_for_model(){

     }

     public function get_model_for_table(){
     	
     }

     /**
      * Get all the models used internally by the framework. This will only return
      * something if the framework_connection setting points to this schema.
      * */
     private function get_framework_models(){
     	 //is framework connection set?
     	 $connection = config('db.framework_connection');

     	 //if not, use the default connection
     	 if(!$connection){
     	 	 $connection = config('db.default_connection');
     	 }

     	 //if default connection is not set, use the first connection
     	 $connection = array_keys(config('db.connections'))[0] ?? '';

     	 //if there is still no connection, fail loudly!
     	 if(!$connection){
     	 	 throw new RuntimeException('Please define at least one database connection for this project!');
     	 }

     	 $schema_class = config('db.schemas')[$connection];

     	 if($schema_class === get_class($this)){
     	 	 return [
     	 	 	 'migrations' => Migration::class,
	 	 	     'sessions' => Session::class,
	 	 	     'framework_queue_failed_jobs' => FailedJob::class,
	 	 	     'framework_queue_jobs' => Job::class,
	 	 	     'framework_queue_job_batches' => JobBatch::class
     	 	 ];
     	 }

     	 return [];
     }
}
