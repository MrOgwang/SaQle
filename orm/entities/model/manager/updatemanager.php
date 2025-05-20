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
use SaQle\Core\Observable\{Observable, ConcreteObservable};
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Orm\Entities\Model\Observer\ModelObserver;
use SaQle\Orm\Entities\Model\Interfaces\IOperationManager;
use SaQle\Orm\Entities\Model\Manager\Utils\ImageUtils;
use Exception;

class UpdateManager implements Observable, IOperationManager{
	 use ImageUtils;
	 
	 use FilterManager{
		 FilterManager::__construct as private __filterConstruct;
	 }

	 use ConcreteObservable {
		 ConcreteObservable::__construct as private __coConstruct;
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

	 private DataContainer $container {
	 	 set(DataContainer $value){
	 	 	 $this->container = $value;
	 	 }

	 	 get => $this->container;
	 }

     /**
      * when an update is done from an object instead of update manager, the object state is tracked here
      * */
	 private array $datastate = [] {
	 	 set(array $value){
	 	 	 $this->datastate = $value;
	 	 }

	 	 get => $this->datastate;
	 }

	 private ?DbContextTracker $ctxtracker = null {
	 	 set(?DbContextTracker $value){
	 	 	 $this->ctxtracker = $value;
	 	 }

	 	 get => $this->ctxtracker;
	 }

	 public function __construct(string $modelclass, array $data){
	 	 Assert::isNonEmptyMap($data, "The data to update is not properly defined!");

	 	 [$dbclass, $table] = $modelclass::get_table_n_dbcontext();
	 	 
	 	 if(!$table || !$dbclass || !$modelclass)
	 	 	 throw new \Exception('Cannot instantiate update manager! Unknown model.');

	 	 $this->table      = $table;
	 	 $this->dbclass    = $dbclass;
	 	 $this->modelclass = $modelclass;
         $this->container  = new DataContainer();
	 	 $this->container->data = $data;

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

	 public function update(bool $multiple = false){
	 	 try{
	 	 	 $pdo        = resolve(Connection::class, DB_CONTEXT_CLASSES[$this->dbclass]);
	 	 	 $modelclass = $this->modelclass;
	 	 	 $model      = $modelclass::state();
	 	 	 $modelmeta  = $model->meta;
	 	 	 $table      = $this->table;
     	     [$clean_data, $file_data] = $model->get_update_data($this->container->data, resolve('request'), $this->datastate);
     	     $this->container->files = [$file_data];
     	     $sql_info  = $this->get_update_sql_info($clean_data);
     	     $operation = new UpdateOperation(
		 	 	 sql:   $sql_info['sql'],
		 	 	 table: $this->table,
		 	 	 data:  $sql_info['data']
		 	 );

		 	 //send a pre update signal to observers
		 	 $preobservers = array_merge(
		 	 	 ModelObserver::get_model_observers('before', 'update', $this->modelclass), 
		 	 	 ModelObserver::get_shared_observers('before', 'update')
		 	 );
	 	     $this->quick_notify(
	 	     	 observers: $preobservers,
	 	     	 code: FeedBack::OK, 
	 	     	 data: [
	 	     	 	 'data'          => $clean_data, 
	 	     	 	 'files'         => $file_data,
	 	     	 	 'table'         => $this->table, 
	 	     	 	 'sql'           => $sql_info['sql'], 
	 	     	 	 'prepared_data' => $sql_info['data'],
	 	     	 	 'dbclass'       => $this->dbclass,
	 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
	 	     	 	 'timestamp'     => time(),
	 	     	 	 'model'         => $this->modelclass
	 	     	 ]
	 	     );
	 	     //update data
		 	 $response = $operation->update($pdo);
		 	 $this->auto_save_files(array_values($this->container->files));
		 	 $updateddata = $modelclass::get()->set_raw_filters($this->get_raw_filters())->all();
		 	 $result = $multiple ? $updateddata : ($updateddata[0] ?? false);

		 	 //send a post update signal to observers
		 	 $postobservers = array_merge(
		 	 	 ModelObserver::get_model_observers('after', 'update', $this->modelclass), 
		 	 	 ModelObserver::get_shared_observers('after', 'update')
		 	 );
	 	     $this->quick_notify(
	 	     	 observers: $postobservers,
	 	     	 code: FeedBack::OK, 
	 	     	 data: [
	 	     	 	 'data'          => $clean_data, 
	 	     	 	 'files'         => $file_data,
	 	     	 	 'table'         => $this->table, 
	 	     	 	 'sql'           => $sql_info['sql'], 
	 	     	 	 'prepared_data' => $sql_info['data'],
	 	     	 	 'dbclass'       => $this->dbclass,
	 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
	 	     	 	 'timestamp'     => time(),
	 	     	 	 'model'         => $this->modelclass,
	 	     	 	 'result'        => $result
	 	     	 ]
	 	     );

	 	     return $result;
     	 }catch(Exception $ex){
     	 	 throw $ex;
     	 }
	 }

	 public function transaction_save(){

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

	 private function get_update_sql_info($clean_data){
	 	 $where_clause = $this->wbuilder->get_where_clause($this->ctxtracker, $this->get_configurations());
	 	 $data = $where_clause->data ? array_merge(array_values($clean_data), $where_clause->data) : array_values($clean_data);
	 	 $database = DB_CONTEXT_CLASSES[$this->dbclass]['name'];
	 	 $table = $this->table;
	 	 $fields = array_keys($clean_data);
	 	 $clause   = $where_clause->clause;
		 $fieldstring = implode(" = ?, ", $fields)." = ?";
			 
		 $sql = "UPDATE {$database}.{$table} SET {$fieldstring}{$clause}";

         return ['sql' => $sql, 'data' => $data];
     }
}
