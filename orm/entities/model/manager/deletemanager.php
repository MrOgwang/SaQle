<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Query\Helpers\FilterManager;
use SaQle\Orm\Operations\Crud\{UpdateOperation, DeleteOperation};
use SaQle\Orm\Connection\Connection;
use SaQle\Orm\Database\Trackers\DbContextTracker;
use SaQle\Core\Observable\{Observable, ConcreteObservable};
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Orm\Entities\Model\Observer\ModelObserver;
use Exception;

class DeleteManager implements Observable {
	 use FilterManager {
		 FilterManager::__construct as private __filterConstruct;
	 }

	 use ConcreteObservable {
		 ConcreteObservable::__construct as private __coConstruct;
	 }

	 private bool $permanently = false {
	 	 set(bool $value){
	 	 	 $this->permanently = $value;
	 	 }

	 	 get => $this->permanently;
	 }

	 private ?string $table = null {
	 	 set(?string $value){
	 	 	 $this->table = $value;
	 	 }

	 	 get => $this->table;
	 }

	 private ?string $dbclass = null {
	 	 set(?string $value){
	 	 	 $this->dbclass = $value;
	 	 }

	 	 get => $this->dbclass;
	 }

	 private ?string $modelclass = null {
	 	 set(?string $value){
	 	 	 $this->modelclass = $value;
	 	 }

	 	 get => $this->modelclass;
	 }

	 private ?DbContextTracker $ctxtracker = null {
	 	 set(?DbContextTracker $value){
	 	 	 $this->ctxtracker = $value;
	 	 }

	 	 get => $this->ctxtracker;
	 }

	 public function __construct(string $modelclass){
	 	 [$dbclass, $table] = $modelclass::get_table_n_dbcontext();
	 	 
	 	 if(!$table || !$dbclass || !$modelclass)
	 	 	 throw new \Exception('Cannot instantiate delete manager! Unknown model.');

	 	 $this->table      = $table;
	 	 $this->dbclass    = $dbclass;
	 	 $this->modelclass = $modelclass;

         $meta = $modelclass::state()->meta;
	 	 $this->setup_ctxtracker(
		 	 table_name:    $this->table,
		 	 table_aliase:  "",
		 	 database_name: DB_CONTEXT_CLASSES[$this->dbclass]['name'],
		 	 field_list:    $meta->actual_column_names,
		 	 ff_settings:   $meta->file_required_fields,
		 	 table_ref:     ''
		 );

         $this->__filterConstruct();
		 $this->__coConstruct();
	 }

	 public function now(){
	 	 try{
	 	 	 $pdo = resolve(Connection::class, DB_CONTEXT_CLASSES[$this->dbclass]);
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
	 	 	 table: $this->table
	 	 );

	 	 //send a pre delete signal to observers
	 	 $preobservers = ModelObserver::get_observers('before', 'delete', $this->modelclass);
 	     $this->quick_notify(
 	     	 code: FeedBack::OK, 
 	     	 data: [
 	     	 	 'table'         => $this->table, 
 	     	 	 'sql'           => $sql_info['sql'], 
 	     	 	 'prepared_data' => $sql_info['data'],
 	     	 	 'dbclass'       => $this->dbclass,
 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
 	     	 	 'timestamp'     => time(),
 	     	 	 'model'         => $this->modelclass
 	     	 ],
 	     	 observers: $preobservers
 	     );

	 	 $response = $operation->update($pdo);
	 	 $result = $response->row_count > 0 ? true : false;

	 	 //send a post delete signal to observers
	 	 $postobservers = ModelObserver::get_observers('after', 'delete', $this->modelclass);
 	     $this->quick_notify(
 	     	 code: FeedBack::OK, 
 	     	 data: [
 	     	 	 'table'         => $this->table, 
 	     	 	 'sql'           => $sql_info['sql'], 
 	     	 	 'prepared_data' => $sql_info['data'],
 	     	 	 'dbclass'       => $this->dbclass,
 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
 	     	 	 'timestamp'     => time(),
 	     	 	 'model'         => $this->modelclass,
 	     	 	 'result'        => $result
 	     	 ],
 	     	 observers: $postobservers
 	     );

	 	 return $result;
     }

     private function hard_delete($pdo){
     	 $sql_info = $this->get_delete_sql_info();
	 	 $operation = new DeleteOperation(
	 	 	 sql:   $sql_info['sql'],
	 	 	 data:  $sql_info['data'],
	 	 	 table: $this->table
	 	 );

	 	 //send a pre delete signal to observers
	 	 $preobservers = ModelObserver::get_observers('before', 'delete', $this->modelclass);
 	     $this->quick_notify(
 	     	 code: FeedBack::OK, 
 	     	 data: [
 	     	 	 'table'         => $this->table, 
 	     	 	 'sql'           => $sql_info['sql'], 
 	     	 	 'prepared_data' => $sql_info['data'],
 	     	 	 'dbclass'       => $this->dbclass,
 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
 	     	 	 'timestamp'     => time(),
 	     	 	 'model'         => $this->modelclass
 	     	 ],
 	     	 observers: $preobservers
 	     );

	 	 $result = $operation->delete($pdo);

	 	 //send a post delete signal to observers
	 	 $postobservers = ModelObserver::get_observers('after', 'delete', $this->modelclass);
 	     $this->quick_notify(
 	     	 code: FeedBack::OK, 
 	     	 data: [
 	     	 	 'table'         => $this->table, 
 	     	 	 'sql'           => $sql_info['sql'], 
 	     	 	 'prepared_data' => $sql_info['data'],
 	     	 	 'dbclass'       => $this->dbclass,
 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
 	     	 	 'timestamp'     => time(),
 	     	 	 'model'         => $this->modelclass,
 	     	 	 'result'        => $result
 	     	 ],
 	     	 observers: $postobservers
 	     );

	 	 return $result;
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

	 private function get_update_sql_info(){
	 	 $where_clause = $this->wbuilder->get_where_clause($this->ctxtracker, $this->get_configurations());
	 	 $data = $where_clause->data ? array_merge([1], $where_clause->data) : [1];
	 	 $database = DB_CONTEXT_CLASSES[$this->dbclass]['name'];
	 	 $table = $this->table;
	 	 $fields = ['deleted'];
	 	 $clause   = $where_clause->clause;
		 $fieldstring = implode(" = ?, ", $fields)." = ?";
			 
		 $sql = "UPDATE {$database}.{$table} SET {$fieldstring}{$clause}";

         return ['sql' => $sql, 'data' => $data];
     }

     private function get_delete_sql_info(){
	 	 $where_clause = $this->wbuilder->get_where_clause($this->ctxtracker, $this->get_configurations());
	 	 $data         = $where_clause->data ?? null;
	 	 $database     = DB_CONTEXT_CLASSES[$this->dbclass]['name'];
	 	 $table        = $this->table;
	 	 $clause       = $where_clause->clause;
		 $sql          = "DELETE FROM {$database}.{$table}{$clause}";

         return ['sql' => $sql, 'data' => $data];
     }
}

?>