<?php
declare(strict_types = 1);

namespace SaQle\Orm\Entities\Model\Manager;

use SaQle\Orm\Entities\Model\Manager\Containers\DataContainer;
use SaQle\Orm\Operations\Crud\InsertOperation;
use SaQle\Core\Assert\Assert;
use SaQle\Orm\Connection\Connection;
use SaQle\Image\Image;
use Exception;

class CreateManager{

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

	 private function commit_file_todisk($folder_path, $file_name, $tmp_name, $crop_dimensions = null, $resize_dimensions = null){
	     $file = $folder_path."/".$file_name;
         if(move_uploaded_file($tmp_name, $file)){
	         if($crop_dimensions){
	             $crop_dimensions = is_array($crop_dimensions) ? $crop_dimensions : [$crop_dimensions];
	             foreach($crop_dimensions as $cd){
	                 $destination_folder = $folder_path."crop/".$cd."px/";
	                 if(!file_exists($destination_folder)){
	                 	mkdir($destination_folder, 0777, true);
	                 }
	                 $destination_folder .= $file_name;
	                 (new Image())->crop_image($file, $cd, $destination_folder);
	             }
	         }
	         if($resize_dimensions){
	             $resize_dimensions = is_array($resize_dimensions) ? $resize_dimensions : [$resize_dimensions];
	             foreach($resize_dimensions as $rd){
	                 $destination_folder = $folder_path."resize/".$rd."px/"; //.$file_name;
	                 if(!file_exists($destination_folder)){
	                 	mkdir($destination_folder, 0777, true);
	                 }
	                 $destination_folder .= $file_name;
	                 (new Image())->resize_image($file, $rd, $destination_folder);
	             }
	         }
         }
	 }

     private function auto_save_files(){
     	 $files = array_values($this->container->files);
     	 foreach($files as $f){
     	 	 if($f){
     	 	 	 foreach($f as $key => $fd){
     	 	 	 	 $crop_dimensions   = $fd->config['crop_dimensions'] ?? null;
			         $resize_dimensions = $fd->config['resize_dimensions'] ?? null;
			         $folder_path       = $fd->config['path'] ?? "";
			         if(is_array($fd->file['name'])){
			             foreach($fd->file['name'] as $n_index1 => $n){
			                 $this->commit_file_todisk($folder_path, $fd->file['name'][$n_index1], $fd->file['tmp_name'][$n_index1], $crop_dimensions, $resize_dimensions);
			             }
			         }else{
			             $this->commit_file_todisk($folder_path, $fd->file['name'], $fd->file['tmp_name'], $crop_dimensions, $resize_dimensions);
			         }
     	 	 	 }
		     }
     	 }
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
		 	 $response = $operation->insert($pdo);
             //save files if any
		 	 $this->auto_save_files();

		 	 $created_rows = $this->get_created_rows($response->last_insert_id, $response->row_count, $modelmeta);

		 	 if(!$created_rows)
     	 	     throw new Exception("Could not create rows!");

     	 	 if($this->container->multiple === true)
     	 	 	return $created_rows;

     	     return $created_rows[0];
     	 }catch(Exception $ex){
     	 	 throw $ex;
     	 }
	 }

	 public function transaction_save(){

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

?>