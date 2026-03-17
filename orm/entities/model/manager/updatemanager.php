<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Core\Exceptions\Model\UpdateOperationFailedException;
use SaQle\Orm\Entities\Model\Manager\Containers\DataContainer;
use SaQle\Core\Assert\Assert;
use SaQle\Orm\Query\References\QueryReferenceMap;
use SaQle\Orm\Query\Helpers\FilterManager;
use SaQle\Orm\Entities\Model\Manager\Utils\{EventUtils, ImageUtils};
use SaQle\Core\Events\ModelEventPhase;
use SaQle\Orm\Entities\Model\Schema\Model;
use Exception;

class UpdateManager extends QueryManager {
	 use ImageUtils, EventUtils;
	 
	 use FilterManager {
		 FilterManager::__construct as private __filterConstruct;
	 }

     private array $clean_data;
	 private DataContainer $container;
	 private ?QueryReferenceMap $query_reference_map = null;

	 public function __construct(Model $model, array $data){
	 	 Assert::isNonEmptyMap($data, "The data to update is not properly defined!");
	 	 
	 	 parent::__construct($model);

         $this->container  = new DataContainer();

	 	 $this->container->data = $data;

	 	 $this->setup_query_reference_map(
		 	 table_name:    $this->model->table->get_table_name(),
		 	 table_aliase:  "",
		 	 database_name: $this->dbdriver->get_config()->get_database(),
		 	 field_list:    $this->model->table->get_table_column_names(),
		 	 ff_settings:   $this->model->table->get_file_required_fields(),
		 	 table_ref:     ''
		 );

		 $this->__filterConstruct();

		 [$clean_data, $file_data] = $this->model->get_update_data($this->container->data, []);
		 $this->container->files = $file_data;
		 $this->clean_data = $clean_data;

		 $this->dbdriver->set_update_query($this);
	 }

	 protected function after_where(string $field_name, $value){
	 	 $this->dbdriver->set_update_query($this);
	 }

	 public function get_query_reference_map(){
	 	 return $this->query_reference_map;
	 }

	 public function get_clean_data(){
	 	return $this->clean_data;
	 }

	 public function now(){
	 	 try{
	 	 	 //connect to the database
	 	 	 $this->dbdriver->connect_with_database();

	 	 	 //get query info
	 	 	 $query_info = $this->get_query_info();

	 	 	 //send a pre update signal to observers
		 	 $named_args = $this->get_named_args('update', $query_info, null, null, $this->clean_data, $this->container->files);
	 	     $this->dispatch_event($this->model::class, ModelEventPhase::UPDATING, $named_args, resolve('request')->user);

             //execute
             [$statement, $response] = array_values($this->dbdriver->execute($query_info['sql'], $query_info['data']));
             $error_code = $statement->errorCode();

             if($response === false || $error_code !== "00000"){
			 	 throw new UpdateOperationFailedException([
			 	 	 'table' => $this->model->table->get_table_name(), 
			 	 	 'statement_error_code' => $error_code
			 	 ]);
			 }

			 //save files
			 //$this->auto_save_files(array_values($this->container->files));

			 //get updates
		 	 $updateddata = $this->model::class::get()->set_raw_filters($this->get_raw_filters())->all();
		 	 $result = $statement->rowCount() > 0 ? $updateddata : ($updateddata[0] ?? false);

		 	 //send a post update signal to observers
		 	 $this->dispatch_event($this->model::class, ModelEventPhase::UPDATED, $named_args, resolve('request')->user, $result);

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
