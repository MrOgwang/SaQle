<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Core\Exceptions\Model\DeleteOperationFailedException;
use SaQle\Orm\Query\Helpers\FilterManager;
use SaQle\Orm\Query\References\QueryReferenceMap;
use SaQle\Orm\Entities\Model\Manager\Utils\EventUtils;
use SaQle\Core\Events\ModelEventPhase;
use SaQle\Orm\Entities\Model\Schema\Model;
use Exception;

class DeleteManager extends QueryManager {
	 use FilterManager {
		 FilterManager::__construct as private __filterConstruct;
	 }

	 use EventUtils;

	 private bool $permanently = false;
	 private ?QueryReferenceMap $query_reference_map = null;

	 public function __construct(Model $model, bool $permanently = false){
	 	 parent::__construct($model);

	 	 $this->permanently = $permanently;

	 	 $this->setup_query_reference_map(
		 	 table_name:    $this->model->meta->get_table_name(),
		 	 table_aliase:  "",
		 	 database_name: $this->dbdriver->get_config()->get_database(),
		 	 field_list:    $this->model->meta->get_table_column_names(),
		 	 ff_settings:   $this->model->meta->get_file_required_fields(),
		 	 table_ref:     ''
		 );

         $this->__filterConstruct();

         $this->update_query();
	 }

	 private function update_query(){
	 	 if($this->permanently){
         	 $this->dbdriver->set_permanent_delete_query($this);
         }else{
         	 $this->dbdriver->set_temporary_delete_query($this);
         }
	 }

	 protected function after_where(string $field_name, $value){
	 	 $this->update_query();
	 }

	 public function get_query_reference_map(){
	 	 return $this->query_reference_map;
	 }

	 public function now(){
	 	 try{
	 	 	 //connect to the database
	 	 	 $this->dbdriver->connect_with_database();

	 	 	 //get query info
	 	 	 $query_info = $this->get_query_info();

	 	 	 //send a pre delete signal to observers
	 	     $named_args = $this->get_named_args('delete', $query_info);
	 	     $this->dispatch_event(
	 	     	 $this->model::class, 
	 	     	 $this->permanently ? ModelEventPhase::DELETING : ModelEventPhase::SOFT_DELETING, 
	 	     	 $named_args, 
	 	     	 resolve('request')->user
	 	     );

             //execute query
	 	     [$statement, $response] = array_values($this->dbdriver->execute($query_info['sql'], $query_info['data']));
             $error_code = $statement->errorCode();

             if($response === false || $error_code !== "00000"){
			 	 throw new DeleteOperationFailedException([
			 	 	 'table' => $this->model->meta->get_table_name(), 
			 	 	 'statement_error_code' => $error_code
			 	 ]);
			 }

			 $result = $statement->rowCount() > 0 ? true : false;

			 //send a post delete signal to observers
	 	     $this->dispatch_event(
	 	     	 $this->model::class, 
	 	     	 $this->permanently ? ModelEventPhase::DELETING : ModelEventPhase::SOFT_DELETED, 
	 	     	 $named_args, 
	 	     	 resolve('request')->user, 
	 	     	 $result
	 	     );

	 	     return $result;

	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }

     private function setup_query_reference_map(string $table_name, string $table_aliase, string $database_name, array $field_list, array $ff_settings, ?string $table_ref = null){
     	 $this->query_reference_map             = new QueryReferenceMap();
	 	 $this->query_reference_map->tables     = array_merge($this->query_reference_map->tables,     [$table_name]);
	 	 $this->query_reference_map->aliases    = array_merge($this->query_reference_map->aliases,    [$table_aliase]);
	 	 $this->query_reference_map->tablerefs  = array_merge($this->query_reference_map->tablerefs,  [$table_ref]);
	 	 $this->query_reference_map->databases  = array_merge($this->query_reference_map->databases,  [$database_name]);
	 	 $this->query_reference_map->fieldrefs  = array_merge($this->query_reference_map->fieldrefs,  [$field_list]);
	 	 $this->query_reference_map->ffsettings = array_merge($this->query_reference_map->ffsettings, [$ff_settings]);
	 }

	 public function get_configurations(){
	 	  return [
	 	  	 /**
	 		 * field name qualification mode: how to qualify field names in the resulting sql statement: options, 
	 		 * 'F-QUALIFY' - the field name is qualified to database_name.table_name.field_name
	 		 * 'H-QUALIFY' - the field name is qualified to table_name.field_name, 
	 		 * 'N-QUALIFY' - the field name is never qualified and is used as is
	 		 * */
	 		 'fnqm' => 'F-QUALIFY',

	 		 /**
	 		  * from table naming mode: how the from table clause is named. options
	 		  * 'N-WITH-A' - name and aliase, if an aliase is avaialbe
	 		  * 'N-ONLY'   - use only the table name, even if an aliase is provided
	 		  * 'A-ONLY'   - use only the aliase name, even if the table name is provided
	 		  * */
	 		 'ftnm' => 'N-WITH-A',

	 		 /**
	 		 * from table qualification mode: how the from table clause is named. options
	 		 * 'F-QUALIFY' - the table name is qualified to database_name.table_name, 
	 		 * 'N-QUALIFY' - the table name is never qualified and is used as is
	 		  * */
	 		 'ftqm' => 'F-QUALIFY'
	 	  ];
	 }
}

