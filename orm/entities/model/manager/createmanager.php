<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Core\Exceptions\Model\InsertOperationFailedException;
use SaQle\Orm\Entities\Model\Manager\Containers\DataContainer;
use SaQle\Core\Assert\Assert;
use SaQle\Orm\Entities\Model\Manager\Utils\{EventUtils, ImageUtils};
use SaQle\Core\Events\ModelEventPhase;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Core\Files\FileCommitter;
use SaQle\Orm\Entities\Model\Collection\ModelCollection;
use SaQle\Orm\Entities\Model\Interfaces\IModel;
use InvalidArgumentException;
use Exception;
use PDO;

class CreateManager extends QueryManager{
	 use ImageUtils, EventUtils;

	 public function __construct(IModel $model){
	 	 parent::__construct($model);

	 	 if($this->model instanceof ModelCollection){
	 	 	 $this->dbdriver->set_multiple_insert_query($this);
	 	 }else{
	 	 	 $this->dbdriver->set_insert_query($this);
	 	 }
	 }

	 private function swap_properties_with_columns(array $data){
	 	 $clean_fields = $this->model instanceof ModelCollection ? 
	 	 $this->model[0]->meta->clean_fields :
	 	 $this->model->meta->clean_fields;

     	 $swapped = [];
     	 foreach($clean_fields as $pk => $pv){
     	 	 $ck = $clean_fields[$pk]->get_column();
     	 	 if(array_key_exists($ck, $data) || array_key_exists($pk, $data)){
     	 	 	 $swapped[$ck] = $data[$ck] ?? ($data[$pk] ?? null);
     	 	 }
     	 }

     	 return $swapped;
     }

     private function is_multiple_inserts(){
     	 return $this->model instanceof ModelCollection;
     }

     private function extract_data(string $type = 'data'){
     	 $origianl_data = $type === 'data' ? $this->model->get_data() : $this->model->get_file_references();
	 	 if(is_assoc($origianl_data)){
             return $this->swap_properties_with_columns($origianl_data);
         }

         $data = [];
         foreach($origianl_data as $d){
         	 $data[] = $this->swap_properties_with_columns($d);
         }

	 	 return $data;
     }

	 public function get_data(){
	 	 return $this->extract_data('data');
	 }

	 public function get_files(){
	 	 return $this->extract_data('files');
	 }

	 public function get_duplicate_action(){
	 	 return $this->model instanceof ModelCollection ? 
	 	 $this->model[0]->meta->action_on_duplicate :
	 	 $this->model->meta->action_on_duplicate;
	 }

	 public function get_primary_key_column(){
	 	 return $this->model instanceof ModelCollection ? 
	 	 $this->model[0]->meta->field_column_refs[$this->model[0]->meta->pk_name] :
	 	 $this->model->meta->field_column_refs[$this->model->meta->pk_name];
	 }

	 public function get_primary_key_type(){
	 	 return $this->model instanceof ModelCollection ? $this->model[0]->meta->pk_type : $this->model->meta->pk_type;
	 }

	 public function get_update_columns(){
	 	 return $this->model->get_update_columns();
	 }

	 public function get_primary_key_values(){
	 	 return $this->model instanceof ModelCollection ? 
	 	 $this->model->pluck_unique($this->model[0]->meta->pk_name) :
	 	 [$this->model->get_data()[$this->model->meta->pk_name]];
	 }

	 public function get_collection_class(){
	 	 return $this->model instanceof ModelCollection ? $this->model::class : $this->model::class::collection_class();
	 }
	 
	 public function now(){
	 	 try{
	 	 	 //connect to the database
	 	 	 $this->dbdriver->connect_with_database();

	 	 	 //get query info
	 	 	 $query_info = $this->get_query_info();

	 	 	 //send a pre insert signal to observers
		 	 $named_args = $this->get_named_args('insert', $query_info, null, null, $this->get_data(), $this->get_files());
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

			 $rows = $this->get_created_rows($statement);

		 	 if(!$rows){
		 	 	 throw new InsertOperationFailedException([
			 	 	 'table' => $this->model->meta->table_name, 
			 	 	 'statement_error_code' => $error_code
			 	 ]);
		 	 }

		 	 $result = $this->is_multiple_inserts() ? $rows : $rows[0];

		 	 //save files if any
		 	 //FileCommitter::commit($this->model, $this->get_files(), $result);

             //send a post insert signal to observers
             $this->dispatch_event($this->model::class, ModelEventPhase::CREATED, $named_args, resolve('request')->user, $result);

     	     return $result;

     	 }catch(Exception $ex){
     	 	 throw $ex;
     	 }
	 }

	 private function get_created_rows($statement){
	 	 //after successful execute
		 if($this->dbdriver->supports_returning()){
		     $rows = $statement->fetchAll(PDO::FETCH_OBJ);

		     $collection_class = $this->get_collection_class();
	 	 	 if($collection_class == GenericModelCollection::class){
	 	 	 	 $rows = $collection_class::from_objects($this->get_model_class(), $rows);
	 	 	 }else{
	 	 	 	 $rows = new $collection_class($rows);
	 	 	 }

	 	 	 return $rows;
		 }

		 if($this->get_primary_key_type() === 'GUID'){
		 	 $pk_values = $this->get_primary_key_values();
		 }else{
		 	 $first_id = (int)$this->dbdriver->get_connection()->lastInsertId();
		     $pk_values = range($first_id, $first_id + $row_count - 1);
		 }

		 $model_class = $this->get_model_class();
		 return $model_class::get()->where($this->get_primary_key_column()."__in", $pk_values)->all();
	 }
}

