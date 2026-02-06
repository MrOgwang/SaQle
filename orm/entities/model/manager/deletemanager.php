<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Query\Helpers\FilterManager;
use SaQle\Orm\Operations\Crud\{UpdateOperation, DeleteOperation};
use SaQle\Orm\Connection\Connection;
use SaQle\Orm\Query\References\QueryReferenceMap;
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Orm\Entities\Model\Interfaces\IOperationManager;
use SaQle\Orm\Entities\Model\Manager\Utils\EventUtils;
use SaQle\Core\Events\ModelEventPhase;
use SaQle\Orm\Entities\Model\Schema\Model;
use Exception;

class DeleteManager implements IOperationManager {
	 use FilterManager {
		 FilterManager::__construct as private __filterConstruct;
	 }

	 use EventUtils;

	 private Model $model;
	 private bool $permanently = false;
	 private ?QueryReferenceMap $query_reference_map = null;

	 public function __construct(Model $model){
	 	 $this->model = $model;
	 	 $this->setup_query_reference_map(
		 	 table_name:    $model->meta->table_name,
		 	 table_aliase:  "",
		 	 database_name: config('connections')[$model->meta->connection_name]['database'],
		 	 field_list:    $model->meta->table_column_names,
		 	 ff_settings:   $model->meta->file_required_fields,
		 	 table_ref:     ''
		 );

         $this->__filterConstruct();
	 }

	 public function now(){
	 	 try{
	 	 	 $pdo = resolve(Connection::class, config('connections')[$this->model->meta->connection_name]);
	 	     return $this->permanently ? $this->hard_delete($pdo) : $this->soft_delete($pdo);
	 	 }catch(Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }

	 public function permanently(){
	 	 $this->permanently = true;
	 	 return $this;
	 }

	 private function soft_delete($pdo){
	 	 $sql_info = $this->get_update_sql_info();
	 	 $operation = new UpdateOperation(
	 	 	 sql:   $sql_info['sql'],
	 	 	 data:  $sql_info['data'],
	 	 	 table: $this->model->meta->table_name
	 	 );

	 	 //send a pre delete signal to observers
	 	 $named_args = $this->get_named_args('delete', $sql_info);
	 	 $this->dispatch_event($this->model::class, ModelEventPhase::SOFT_DELETING, $named_args, resolve('request')->user);

	 	 $response = $operation->update($pdo);
	 	 $result = $response->row_count > 0 ? true : false;

	 	 //send a post delete signal to observers
	 	 $this->dispatch_event($this->model::class, ModelEventPhase::SOFT_DELETED, $named_args, resolve('request')->user, $result);

	 	 return $result;
     }

     private function hard_delete($pdo){
     	 $sql_info = $this->get_delete_sql_info();
	 	 $operation = new DeleteOperation(
	 	 	 sql:   $sql_info['sql'],
	 	 	 data:  $sql_info['data'],
	 	 	 table: $this->model->meta->table_name
	 	 );

	 	 //send a pre delete signal to observers
	 	 $named_args = $this->get_named_args('delete', $sql_info);
	 	 $this->dispatch_event($this->model::class, ModelEventPhase::DELETING, $named_args, resolve('request')->user);

	 	 $result = $operation->delete($pdo);

	 	 //send a post delete signal to observers
	 	 $this->dispatch_event($this->model::class, ModelEventPhase::DELETED, $named_args, resolve('request')->user, $result);

	 	 return $result;
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

	 private function get_update_sql_info(){
	 	 $where_clause = $this->wbuilder->get_where_clause($this->query_reference_map, $this->get_configurations());
	 	 $data = $where_clause->data ? array_merge([1], $where_clause->data) : [1];
	 	 $database = config('connections')[$this->model->meta->connection_name]['database'];
	 	 $table = $this->model->meta->table_name;
	 	 $fields = ['deleted'];
	 	 $clause   = $where_clause->clause;
		 $fieldstring = implode(" = ?, ", $fields)." = ?";
			 
		 $sql = "UPDATE {$database}.{$table} SET {$fieldstring}{$clause}";

         return ['sql' => $sql, 'data' => $data];
     }

     private function get_delete_sql_info(){
	 	 $where_clause = $this->wbuilder->get_where_clause($this->query_reference_map, $this->get_configurations());
	 	 $data         = $where_clause->data ?? null;
	 	 $database     = config('connections')[$this->model->meta->connection_name]['database'];
	 	 $table        = $this->model->meta->table_name;
	 	 $clause       = $where_clause->clause;
		 $sql          = "DELETE FROM {$database}.{$table}{$clause}";

         return ['sql' => $sql, 'data' => $data];
     }

     public function get_sql_info(){
     	 return $this->permanently ? $this->get_delete_sql_info() : $this->get_update_sql_info();
     }
}

