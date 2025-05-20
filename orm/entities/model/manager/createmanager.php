<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Entities\Model\Manager\Containers\DataContainer;
use SaQle\Orm\Operations\Crud\InsertOperation;
use SaQle\Core\Assert\Assert;
use SaQle\Orm\Connection\Connection;
use SaQle\Image\Image;
use SaQle\Core\Observable\{Observable, ConcreteObservable};
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Orm\Entities\Model\Observer\ModelObserver;
use SaQle\Orm\Entities\Model\Interfaces\IOperationManager;
use SaQle\Orm\Entities\Model\Manager\Utils\ImageUtils;
use Exception;

class CreateManager implements Observable, IOperationManager {
	 use ImageUtils;

	 use ConcreteObservable{
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

	 public function __construct(string $modelclass, array $data){
	 	 if(empty($data))
	 	 	 throw new \Exception('You did not pass in data to add!');

	 	 [$dbclass, $table] = $modelclass::get_table_n_dbcontext();

	 	 if(!$table || !$dbclass || !$modelclass)
	 	 	 throw new \Exception('Cannot instantiate create manager! Unknown model.');

	 	 $this->table      = $table;
	 	 $this->dbclass    = $dbclass;
	 	 $this->modelclass = $modelclass;
	 	 $this->container  = new DataContainer();
	 	 $this->set_data($data);
	 	 $this->__coConstruct();
	 }

     private function extract_row(array $row, int $index = 0){
     	 Assert::isNonEmptyMap($row, "The data in one or more rows is not properly defined!");

         $modelclass = $this->modelclass;
	 	 $model = new $modelclass(...$row);
 	 	 $modelmeta = $model->meta;
 	 	 [$clean_data, $file_data] = $model->get_insert_data(resolve('request'));
         $entry_key = spl_object_hash((object)$clean_data).$index;

         return [$entry_key, $clean_data, $file_data, $clean_data[$modelmeta->pk_name]];
     }

	 private function set_data(array $data){
	 	 $modelclass = $this->modelclass;
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
	 	 	 $pdo        = resolve(Connection::class, DB_CONTEXT_CLASSES[$this->dbclass]);
	 	 	 $modelclass = $this->modelclass;
	 	 	 $model      = $modelclass::state();
	 	 	 $modelmeta  = $model->meta;
 	     	 $sql_info   = $this->get_insert_sql_info($modelmeta);
 	     	 $pk_type    = $modelmeta->pk_type;
 	     	 $operation  = new InsertOperation( 
 	     	 	 prmkeytype: $pk_type,
		 	 	 table:      $this->table,
		 	 	 sql:        $sql_info['sql'],
		 	 	 data:       $sql_info['data']
		 	 );

		 	 //send a pre insert signal to observers
		 	 $preobservers = array_merge(
		 	 	 ModelObserver::get_model_observers('before', 'insert', $this->modelclass),
		 	 	 ModelObserver::get_shared_observers('before', 'insert')
		 	 );
	 	     $this->quick_notify(
	 	     	 observers: $preobservers,
	 	     	 code: FeedBack::OK, 
	 	     	 data: [
	 	     	 	 'data'          => $this->container->data, 
	 	     	 	 'files'         => $this->container->files,
	 	     	 	 'table'         => $this->table, 
	 	     	 	 'sql'           => $sql_info['sql'], 
	 	     	 	 'prepared_data' => $sql_info['data'],
	 	     	 	 'dbclass'       => $this->dbclass,
	 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
	 	     	 	 'timestamp'     => time(),
	 	     	 	 'model'         => $this->modelclass
	 	     	 ]
	 	     );
             //insert data
		 	 $response = $operation->insert($pdo);
             //save files if any
		 	 $this->auto_save_files(array_values($this->container->files));
		 	 //get inserted data
		 	 $created_rows = $this->get_created_rows($response->last_insert_id, $response->row_count, $modelmeta);

		 	 if(!$created_rows)
     	 	     throw new Exception("Could not create rows!");

     	 	 $result = $this->container->multiple === true ? $created_rows : $created_rows[0];

             //send a post insert signal to observers
     	 	 $postobservers = array_merge(
     	 	 	 ModelObserver::get_model_observers('after', 'insert', $this->modelclass), 
     	 	 	 ModelObserver::get_shared_observers('after', 'insert')
     	 	 );
     	 	 $this->quick_notify(
     	 	 	 observers: $postobservers,
	 	     	 code: FeedBack::OK, 
	 	     	 data: [
	 	     	 	 'data'          => $this->container->data, 
	 	     	 	 'files'         => $this->container->files,
	 	     	 	 'table'         => $this->table, 
	 	     	 	 'sql'           => $sql_info['sql'], 
	 	     	 	 'prepared_data' => $sql_info['data'],
	 	     	 	 'dbclass'       => $this->dbclass,
	 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
	 	     	 	 'timestamp'     => time(),
	 	     	 	 'result'        => $result,
	 	     	 	 'model'         => $this->modelclass
	 	     	 ]
	 	     );

     	     return $result;
     	 }catch(Exception $ex){
     	 	 throw $ex;
     	 }
	 }

	 private function get_insert_sql_info($modelmeta){
     	 $fields        = array_keys(array_values($this->container->data)[0]);
		 $data          = array_values($this->container->data);
		 $values        = [];
		 $row_count     = count($data);
		 foreach($data as $row){
			 $values[]  = array_values($row);
		 }
		 $database      = DB_CONTEXT_CLASSES[$this->dbclass]['name'];
		 $table         = $this->table;
		 $fieldstring   = implode(", ", $fields);
		 $valstring     = str_repeat('?, ', count($fields) - 1). '?';
         $prepared_data = array_merge(...$values);
		 if($modelmeta->action_on_duplicate === 'ABORT_WITH_ERROR'){
		     $sql = "INSERT INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1). "($valstring)";
		 }elseif($modelmeta->action_on_duplicate === 'INSERT_MINUS_DUPLICATE'){
		 	 $sql = "INSERT IGNORE INTO {$database}.{$table} ({$fieldstring}) VALUES ".str_repeat("($valstring), ", $row_count - 1)."($valstring)";
		 }elseif($modelmeta->action_on_duplicate === 'UPDATE_ON_DUPLICATE'){
		 	 $exclude = array_merge($modelmeta->unique_fields, [$modelmeta->pk_name]);
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
     	 $modelclass    = $this->modelclass;
	 	 $model         = $modelclass::state();
     	 $modelmeta     = $model->meta;

     	 if(!empty($modelmeta->unique_fields)){
     	 	 $unique_values = [];
     	 	 foreach($modelmeta->unique_fields as $uf){
     	 	 	 $unique_values[$uf] = array_column($this->container->data, $uf);
     	 	 }

     	 	 $readmanager = $modelclass::get();
     	 	 foreach($modelmeta->unique_fields as $uf){
     	 	 	 $readmanager->where($uf."__in", $unique_values[$uf]);
     	 	 }

     	 	 return $readmanager->all();
     	 }else{
     	 	 $pkvalues = [];
     	 	 if($modelmeta->pk_type === 'GUID'){
		 	 	 $pkvalues = array_values($this->container->pkvalues);
		 	 }else{
				 for($i = 0; $i < $row_count; $i++){
				     $pkvalues[] = $last_insert_id + $i;
				 }
		 	 }
		 	 return $modelclass::get()->where($modelmeta->pk_name."__in", $pkvalues)->all();
     	 }
     }
}

