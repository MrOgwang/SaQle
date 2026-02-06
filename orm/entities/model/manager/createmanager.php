<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Entities\Model\Manager\Containers\DataContainer;
use SaQle\Orm\Operations\Crud\InsertOperation;
use SaQle\Core\Assert\Assert;
use SaQle\Orm\Connection\Connection;
use SaQle\Image\Image;
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Orm\Entities\Model\Interfaces\IOperationManager;
use SaQle\Orm\Entities\Model\Manager\Utils\{EventUtils, ImageUtils};
use SaQle\Core\Events\ModelEventPhase;
use SaQle\Orm\Entities\Model\Schema\TableInfo;
use SaQle\Orm\Entities\Model\Schema\Model;
use Exception;

class CreateManager implements IOperationManager {
	 use ImageUtils, EventUtils;

	 private Model $model;
	 private DataContainer $container; 

	 public function __construct(Model $model, array $data){
	 	 if(empty($data))
	 	 	 throw new Exception('You did not pass in data to add!');

	 	 $this->model = $model;
	 	 $this->container = new DataContainer();
	 	 $this->set_data($data);
	 }

     private function extract_row(array $row, int $index = 0){
     	 Assert::isNonEmptyMap($row, "The data in one or more rows is not properly defined!");

         $modelclass = $this->model::class;
	 	 $model = new $modelclass(...$row);
 	 	 [$clean_data, $file_data] = $model->get_insert_data(resolve('request'));

         $entry_key = spl_object_hash((object)$clean_data).$index;

         return [$entry_key, $clean_data, $file_data, $clean_data[$this->model->meta->pk_name]];
     }

	 private function set_data(array $data){
	 	 $pkvalues   = [];
	 	 $files      = [];
	 	 $insertdata = [];
	 	 if(array_is_list($data)){
	 	 	 $this->container->multiple = true;
	 	 	 foreach($data as $index => $row){
	 	 	 	 [$entry_key, $clean_data, $file_data, $pkvalue] = $this->extract_row($row, $index);

                 $pkvalues[$entry_key]   = $pkvalue;
     	         $files[$entry_key]      = $file_data;
     	         $insertdata[$entry_key] = $clean_data;
	 	 	 }
	 	 }else{
	 	 	 [$entry_key, $clean_data, $file_data, $pkvalue] = $this->extract_row($data, 0);

	 	 	 $pkvalues[$entry_key]   = $pkvalue;
     	     $files[$entry_key]      = $file_data;
     	     $insertdata[$entry_key] = $clean_data;
	 	 }

	 	 $this->container->pkvalues = $pkvalues;
	 	 $this->container->files    = $files;
	 	 $this->container->data     = $insertdata;
	 }
	 
	 public function save(){
	 	 try{
	 	 	 $pdo        = resolve(Connection::class, config('connections')[$this->model->meta->connection_name]);
 	     	 $sql_info   = $this->get_sql_info($this->model->meta);
 	     	 $operation  = new InsertOperation( 
 	     	 	 prmkeytype: $this->model->meta->pk_type,
		 	 	 table:      $this->model->meta->table_name,
		 	 	 sql:        $sql_info['sql'],
		 	 	 data:       $sql_info['data']
		 	 );

		 	 //send a pre insert signal to observers
		 	 $named_args = $this->get_named_args('insert', $sql_info, null, null, $this->container->data, $this->container->files);
		 	 $this->dispatch_event($this->model::class, ModelEventPhase::CREATING, $named_args, resolve('request')->user);

             //insert data
		 	 $response = $operation->insert($pdo);
             
             //save files if any
		 	 $this->auto_save_files(array_values($this->container->files));
		 	 //get inserted data
		 	 $created_rows = $this->get_created_rows($response->last_insert_id, $response->row_count, $this->model->meta);

		 	 if(!$created_rows)
     	 	     throw new Exception("Could not create rows!");

     	 	 $result = $this->container->multiple === true ? $created_rows : $created_rows[0];

             //send a post insert signal to observers
             $this->dispatch_event($this->model::class, ModelEventPhase::CREATED, $named_args, resolve('request')->user, $result);

     	     return $result;
     	 }catch(Exception $ex){
     	 	 throw $ex;
     	 }
	 }

	 private function get_sql_info(TableInfo $modelmeta){
     	 $fields        = array_keys(array_values($this->container->data)[0]);
		 $data          = array_values($this->container->data);
		 $values        = [];
		 $row_count     = count($data);
		 foreach($data as $row){
			 $values[]  = array_values($row);
		 }
		 $database      = config('connections')[$modelmeta->connection_name]['database'];
		 $table         = $modelmeta->table_name;
		 $fieldstring   = implode(", ", $fields);
		 $valstring     = str_repeat('?, ', count($fields) - 1). '?';
         $prepared_data = array_merge(...$values);
		 if($modelmeta->action_on_duplicate === 'ABORT_WITH_ERROR'){
		     $sql = "INSERT INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1). "($valstring)";
		 }elseif($modelmeta->action_on_duplicate === 'INSERT_MINUS_DUPLICATE'){
		 	 $sql = "INSERT IGNORE INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1)."($valstring)";
		 }elseif($modelmeta->action_on_duplicate === 'UPDATE_ON_DUPLICATE'){
		 	 $exclude = array_merge($modelclass::get_unique_field_names(), [$modelclass::get_pk_name()]);
		 	 $toupdate = array_map(function($f){
		 	 	 return "$f = VALUES($f)";
		 	 }, array_diff($fields, $exclude));
		 	 $sql = "INSERT INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1)."($valstring) ON DUPLICATE KEY UPDATE ".implode(', ', $toupdate);
		 }elseif($modelmeta->action_on_duplicate === 'RETURN_EXISTING'){
		 	 $sql = "INSERT INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1)."($valstring) ON DUPLICATE KEY UPDATE {$modelmeta->pk_name} = {$modelmeta->pk_name}";
		 }
         return ['sql' => $sql, 'data' => $prepared_data];
     }

     private function get_created_rows($last_insert_id, $row_count, $modelmeta){
     	 $modelclass    = $this->model::class;
	 	 $model         = $modelclass::make();
     	 $modelmeta     = $model->meta;

     	 $pkvalues = [];
 	 	 if($modelclass::get_pk_type() === 'GUID'){
	 	 	 $pkvalues = array_values($this->container->pkvalues);
	 	 }else{
			 for($i = 0; $i < $row_count; $i++){
			     $pkvalues[] = $last_insert_id + $i;
			 }
	 	 }
	 	 return $modelclass::using($this->model->meta->connection_name)->get()->where($modelclass::get_pk_name()."__in", $pkvalues)->all();
     }
}

