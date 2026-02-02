<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Entities\Model\Manager\Containers\DataContainer;
use SaQle\Orm\Operations\Crud\UpdateOperation;
use SaQle\Core\Assert\Assert;
use SaQle\Orm\Connection\Connection;
use SaQle\Image\Image;
use SaQle\Orm\Database\Trackers\DbContextTracker;
use SaQle\Orm\Query\Helpers\FilterManager;
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Orm\Entities\Model\Interfaces\IOperationManager;
use SaQle\Orm\Entities\Model\Manager\Utils\{EventUtils, ImageUtils};
use SaQle\Core\Events\ModelEventPhase;
use SaQle\Orm\Entities\Model\Schema\Model;
use Exception;

class UpdateManager implements IOperationManager{
	 use ImageUtils, EventUtils;
	 
	 use FilterManager{
		 FilterManager::__construct as private __filterConstruct;
	 }

	 private Model $model;
	 private DataContainer $container;
     //when an update is done from an object instead of update manager, the object state is tracked here
	 private array $datastate = [];
	 private ?DbContextTracker $ctxtracker = null;

	 public function __construct(Model $model, array $data){
	 	 Assert::isNonEmptyMap($data, "The data to update is not properly defined!");

	 	 $this->model = $model;
         $this->container  = new DataContainer();
	 	 $this->container->data = $data;

	 	 $this->setup_ctxtracker(
		 	 table_name:    $this->table,
		 	 table_aliase:  "",
		 	 database_name: config('connections')[$this->model->meta->connection_name]['database'],
		 	 field_list:    $this->model->meta->table_column_names,
		 	 ff_settings:   $this->model->meta->file_required_fields,
		 	 table_ref:     ''
		 );

		 $this->__filterConstruct();
	 }

	 public function update(bool $multiple = false){
	 	 try{
	 	 	 $pdo = resolve(Connection::class, config('connections')[$this->model->meta->connection_name]);
     	     [$clean_data, $file_data] = $this->model->get_update_data($this->container->data, resolve('request'), $this->datastate);
     	     $this->container->files = [$file_data];
     	     $sql_info  = $this->get_sql_info($clean_data);
     	     $operation = new UpdateOperation(
		 	 	 sql:   $sql_info['sql'],
		 	 	 table: $this->model->meta->table_name,
		 	 	 data:  $sql_info['data']
		 	 );

		 	 //send a pre update signal to observers
		 	 $named_args = $this->get_named_args('update', $sql_info, null, null, $clean_data, $file_data);
	 	     $this->dispatch_event($this->model::class, ModelEventPhase::UPDATING, $named_args, resolve('request')->user);

	 	     //update data
		 	 $response = $operation->update($pdo);
		 	 $this->auto_save_files(array_values($this->container->files));
		 	 $updateddata = $modelclass::get()->set_raw_filters($this->get_raw_filters())->all();
		 	 $result = $multiple ? $updateddata : ($updateddata[0] ?? false);

		 	 //send a post update signal to observers
		 	 $this->dispatch_event($this->model::class, ModelEventPhase::UPDATED, $named_args, resolve('request')->user, $result);

	 	     return $result;
     	 }catch(Exception $ex){
     	 	 throw $ex;
     	 }
	 }

     public function set_data_state(array $datastate){
     	 $this->datastate = $datastate;
     	 return $this;
     }

     private function setup_ctxtracker(string $table_name, string $table_aliase, string $database_name, array $field_list, array $ff_settings, ?string $table_ref = null){
     	 $this->ctxtracker             = new DbContextTracker();
	 	 $this->ctxtracker->tables     = array_merge($this->ctxtracker->tables,     [$table_name]);
	 	 $this->ctxtracker->aliases    = array_merge($this->ctxtracker->aliases,    [$table_aliase]);
	 	 $this->ctxtracker->tablerefs  = array_merge($this->ctxtracker->tablerefs,  [$table_ref]);
	 	 $this->ctxtracker->databases  = array_merge($this->ctxtracker->databases,  [$database_name]);
	 	 $this->ctxtracker->fieldrefs  = array_merge($this->ctxtracker->fieldrefs,  [$field_list]);
	 	 $this->ctxtracker->ffsettings = array_merge($this->ctxtracker->ffsettings, [$ff_settings]);
	 }

	 private function get_configurations(){
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

	 private function get_sql_info(array $clean_data){
	 	 $where_clause = $this->wbuilder->get_where_clause($this->ctxtracker, $this->get_configurations());
	 	 $data = $where_clause->data ? array_merge(array_values($clean_data), $where_clause->data) : array_values($clean_data);
	 	 $database = config('connections')[$this->model->meta->connection_name]['database'];
	 	 $table = $this->model->meta->table_name;
	 	 $fields = array_keys($clean_data);
	 	 $clause   = $where_clause->clause;
		 $fieldstring = implode(" = ?, ", $fields)." = ?";
			 
		 $sql = "UPDATE {$database}.{$table} SET {$fieldstring}{$clause}";

         return ['sql' => $sql, 'data' => $data];
     }
}
