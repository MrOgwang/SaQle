<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Core\Exceptions\Model\InsertOperationFailedException;
use SaQle\Orm\Entities\Model\Manager\Containers\DataContainer;
use SaQle\Core\Assert\Assert;
use SaQle\Orm\Entities\Model\Manager\Utils\{EventUtils, ImageUtils};
use SaQle\Core\Events\ModelEventPhase;
use SaQle\Orm\Entities\Model\Interfaces\IModel;
use SaQle\Core\Files\FileCommitter;
use InvalidArgumentException;
use Exception;

class CreateManager extends QueryManager {
	 use ImageUtils, EventUtils;

	 private DataContainer $container; 

	 public function __construct(IModel $model, array $data){

	 	 $this->assert_valid_data($data);

	 	 parent::__construct($model);
	 	 $this->container = new DataContainer();
	 	 $this->prepare_insert_data($data);

	 	 $this->dbdriver->set_insert_query($this);
	 }

	 private function assert_valid_data(array $data): void {
	     //Case 1: single object (associative array)
	     if(is_assoc($data)) {
	         return;
	     }

	     //Case 2: many objects (array of associative arrays)
	     foreach ($data as $item) {
	         if(!is_array($item) || !is_assoc($item)) {
	            throw new InvalidArgumentException(
	                "The data to insert is not properly defined!"
	            );
	         }
	     }

	     //Empty array is ambiguous → reject
	     if($data === []){
	         throw new InvalidArgumentException(
	            'Cannot insert empty data!'
	         );
	     }
	 }

     private function extract_row(array $row, int $index = 0){
     	 Assert::isNonEmptyMap($row, "The data in one or more rows is not properly defined!");

         $modelclass = $this->model::class;
	 	 $model = new $modelclass(...$row);
 	 	 [$clean_data, $file_data] = $model->get_insert_data(resolve('request'));

         $entry_key = spl_object_hash((object)$clean_data).$index;

         return [$entry_key, $clean_data, $file_data, $clean_data[$this->model->meta->pk_name]];
     }

	 private function prepare_insert_data(array $data){
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
	 
	 public function now(){
	 	 try{
	 	 	 //connect to the database
	 	 	 $this->dbdriver->connect_with_database();

	 	 	 //get query info
	 	 	 $query_info = $this->get_query_info();

	 	 	 //send a pre insert signal to observers
		 	 $named_args = $this->get_named_args('insert', $query_info, null, null, $this->container->data, $this->container->files);
		 	 $this->dispatch_event($this->model::class, ModelEventPhase::CREATING, $named_args, resolve('request')->user);

		 	 //execute
             [$statement, $response] = array_values($this->dbdriver->execute($query_info['sql'], $query_info['data']));
             $error_code = $statement->errorCode();

             if($response === false || $error_code !== "00000"){
			 	 throw new InsertOperationFailedException([
			 	 	 'table' => $this->model->meta->table_name, 
			 	 	 'statement_error_code' => $error_code
			 	 ]);
			 }

		 	 //get inserted data
		 	 $created_rows = $this->get_created_rows(
		 	 	 $this->dbdriver->get_connection()->lastInsertId(), 
		 	 	 $statement->rowCount()
		 	 );

		 	 if(!$created_rows){
		 	 	 throw new InsertOperationFailedException([
			 	 	 'table' => $this->model->meta->table_name, 
			 	 	 'statement_error_code' => $error_code
			 	 ]);
		 	 }

		 	 //save files if any
		 	 //$this->auto_save_files(array_values($this->container->files));
		 	 FileCommitter::commit($this->model, $this->model->files, $created_rows[0]);

     	 	 $result = $this->container->multiple === true ? $created_rows : $created_rows[0];

             //send a post insert signal to observers
             $this->dispatch_event($this->model::class, ModelEventPhase::CREATED, $named_args, resolve('request')->user, $result);

     	     return $result;

     	 }catch(Exception $ex){
     	 	 throw $ex;
     	 }
	 }

     private function get_created_rows($last_insert_id, $row_count){
     	 $modelclass = $this->model::class;
 	 	 if($modelclass::get_pk_type() === 'GUID'){
	 	 	 $pkvalues = array_values($this->container->pkvalues);
	 	 }else{
			 for($i = 0; $i < $row_count; $i++){
			     $pkvalues[] = $last_insert_id + $i;
			 }
	 	 }

	 	 return $modelclass::using($this->model->meta->connection_name)->get()->where($modelclass::get_pk_name()."__in", $pkvalues)->all();
     }

     public function get_container(){
     	 return $this->container;
     }
}

