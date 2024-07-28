<?php
 namespace SaQle\Dao\Model\Manager;

 use SaQle\Dao\Commands\Crud\{SelectCommand, InsertCommand, DeleteCommand, UpdateCommand, TotalCommand, TableCreateCommand, RunCommand};
 use SaQle\Dao\Operations\Crud\{SelectOperation, InsertOperation, DeleteOperation, UpdateOperation, TotalOperation, TableCreateOperation, RunOperation};
 use SaQle\Dao\Commands\ICommand;
 use SaQle\Dao\Model\Exceptions\NullObjectException;
 use function SaQle\Exceptions\{modelnotfoundexception};
 use SaQle\Dao\Model\Model;
 use SaQle\Image\Image;
 use SaQle\Dao\Field\Attributes\NavigationKey;
 use SaQle\Commons\{DateUtils, UrlUtils, StringUtils};
 use SaQle\Services\Container\ContainerService;
 use SaQle\Services\Container\Cf;
 use SaQle\Dao\Field\Relations\One2One;

class ModelManager extends IModelManager{
	 use DateUtils, UrlUtils, StringUtils;
	 private ICommand $_crud_command;
	 // utilities

	 private function get_join_clause(){
	 	 /*acquire join manager*/
	 	 $join_manager = $this->get_join_manager();
	 	 /*register current context tracker with join manager*/
	 	 $join_manager->set_context_tracker($this->get_context_tracker());
	 	 /*get and return the join clause*/
	 	 return $join_manager->construct_join_clause();
	 }

	 private function get_limit_clause(){
	 	 /*acquire limit manager*/
	 	 $limit_manager = $this->get_limit_manager();
	 	 /*get and return the jlimit clause*/
	 	 return $limit_manager->construct_limit_clause();
	 }

	 private function get_order_clause(){
	 	 /*acquire order manager*/
	 	 $order_manager = $this->get_order_manager();
	 	 /*get and return the order clause*/
	 	 return $order_manager->construct_order_clause();
	 }

	 private function get_selected(){
	 	 /*acquire select manager*/
	 	 $select_manager = $this->get_select_manager();
	 	 $selected = $select_manager->get_selected();
	 	 if(count($selected) === 0){
	 	 	$selected[] = "*";
	 	 }
	 	 return implode(", ", $selected);
	 }

     //filtering
	 public function where(string $field_name, $value){
	 	 $this->fmanager->simple_aggregate([$field_name, $value, "&"]);
	 	 return $this;
	 }
	 public function or_where(string $field_name, $value){
	 	 $this->fmanager->simple_aggregate([$field_name, $value, "|"]);
	 	 return $this;
	 }
	 public function gwhere($callback){
	 	 $this->fmanager->group_aggregate($this, $callback, '&');
	 	 return $this;
	 }
	 public function or_gwhere($callback){
	 	 $this->fmanager->group_aggregate($this, $callback, '|');
	 	 return $this;
	 }

	 //joining
     private function add_join(string $type, string $table, ?string $from = null, ?string $to = null, ?string $as = null, ?string $database = null){
     	 $this->register_joining_model($table, $as);

     	 #if the database is not provided, assume the joining table is in the same database as the base table.
     	 $database = $database ?: $this->get_context_tracker()->find_database_name(0);

     	 #if the from/to field is not provided, assume it is the primary key of the base table
     	 $model   = $this->get_model($table);
		 $pk_name = $model->get_pk_name();

     	 $from = $from ?: $pk_name;
     	 $to   = $to   ?: $pk_name;
     	 $this->get_join_manager()->add_join(type: $type, table: $table, from: $from, to: $to, as: $as, database: $database);
     }
	 public function inner_join(string $table, ?string $from = null, ?string $to = null, ?string $as = null, ?string $database = null){
	 	 $this->add_join(type: 'INNER JOIN', table: $table, from: $from, to: $to, as: $as, database: $database);
	     return $this;
	 }
	 public function outer_join(string $table, ?string $from = null, ?string $to = null, ?string $as = null, ?string $database = null){
	 	 $this->add_join(type: 'OUTER JOIN', table: $table, from: $from, to: $to, as: $as, database: $database);
	     return $this;
	 }
	 public function left_outer_join(string $table, ?string $from = null, ?string $to = null, ?string $as = null, ?string $database = null){
	 	 $this->add_join(type: 'LEFT OUTER JOIN', table: $table, from: $from, to: $to, as: $as, database: $database);
	     return $this;
	 }
	 public function right_outer_join(string $table, ?string $from = null, ?string $to = null, ?string $as = null, ?string $database = null){
	 	 $this->add_join(type: 'RIGHT OUTER JOIN', table: $table, from: $from, to: $to, as: $as, database: $database);
	     return $this;
	 }
	 public function full_outer_join(string $table, ?string $from = null, ?string $to = null, ?string $as = null, ?string $database = null){
	 	 $this->add_join(type: 'FULL OUTER JOIN', table: $table, from: $from, to: $to, as: $as, database: $database);
	     return $this;
	 }

     //selecting

     /**
     * Specify model fields to return in a select operation. Fields can be qualified with . operator. Example users.first_name;
     * @param array
     * @throw DatabaseNotFoundException
     * @throw ModelNotFoundException
     * @throw FieldNotFoundException
     */
     public function select(array $fields){
     	$select_manager = $this->get_select_manager();
	 	$select_manager->set_selected($fields);
     	return $this;
     }
	 
	 //grouping
	 public function group_by(string $field_name, ?string $table_name = null, ?string $database_name = null){

	 }

	 //limiting
     /**
     * Limit the number of rows returned by a select query.
     * @param int $page - the page to fetch
     * @param int records - the number of records to fetch.
     */
	 public function limit(int $page = 1, int $records = 10){
	 	$this->set_limit(page: $page, records: $records);
	 	return $this;
	 }

	 //ordering
	 /**
     * Order the results returned by a select query.
     * @param array $fields - the field names to order based on
     * @param string $direction - order ASC or DESC
     */
	 public function order(array $fields, string $direction = "ASC"){
	 	$this->set_order(fields: $fields, direction: $direction);
	 	return $this;
	 }

	 //fetching
	 /*
	 * return all the rows found.
	 */
	 public function all(bool $todao = false){
	 	 return $this->get($todao);
	 }

	 /*
	 * return the first row if its available otherwise throw an error
	 */
	 public function first(bool $todao = false){
	 	 $response = $this->get($todao);
	 	 if(!$response){
	 	 	$table = $this->get_context_tracker()->find_table_name(0);
	 	 	throw new NullObjectException(table: $table);
	 	 }
	 	 return $response[0];
	 }

	 /*
	    return the first row if its available otherwise return null
	 */
	 public function first_or_default(bool $todao = false){
	 	 $response = $this->get($todao);
	 	 return $response ? $response[0] : null;
	 }

     /*
     * reteurn the last row if its available otherwise throw an error
     */
	 public function last(bool $todao = false){
	 	 $response = $this->get($todao);
	 	 if(!$response){
	 	 	throw NullObjectException(table: $this->get_context_tracker()->find_table_name(0));
	 	 }
	 	 return $response[count($response) - 1];
	 }

	 /*
	 * return the last row if its available otherwise return null
	 */
	 public function last_or_default(bool $todao = false){
	 	 $response = $this->get($todao);
	 	 return $response ? $response[count($response) - 1] : null;
	 }

	 private function get(bool $todao = false){
	 	 #acquire the table being processed
	 	 $table_name   = $this->get_context_tracker()->find_table_name(0);
	 	 #acquire the dao model.
	 	 $model_instance = $this->get_model($table_name);
	 	 $model_class    = $model_instance::class;

	 	 #apply a default limit if one was not provided
	 	 $limit_clause = $this->get_limit_clause();
	 	 if(!$limit_clause){
	 	 	 $this->limit();
	 	 	 $limit_clause = $this->get_limit_clause();
	 	 }

	 	 #apply a soft delete filter if the current dao has a soft delete attribute
	 	 if($model_instance->get_soft_delete() && !$this->_ignore_soft_delete){
     	 	 $this->where($table_name.'.deleted__eq', 0);
     	 }

	 	 #setup a select command
	 	 $this->crud_command = new SelectCommand(
	 	 	 new SelectOperation($this->get_connection()),
	 	 	 select_clause: $this->get_selected(),
	 	 	 where_clause:  $this->fmanager->get_where_clause($this->get_context_tracker()),
	 	 	 join_clause:   $this->get_join_clause(),
	 	 	 limit_clause:  $limit_clause,
	 	 	 order_clause:  $this->get_order_clause(),
	 	 	 table_name:    $table_name,
	 	 	 table_aliase:  $this->get_context_tracker()->find_table_aliase(0),
	 	 	 database_name: $this->get_context_tracker()->find_database_name(0),
	 	 	 todao:         $todao,
	 	 	 daoclass:      $model_class
	 	 );

	 	 #execute command and return response
	 	 $rows = $this->crud_command->execute();

         #get explicit includes
	 	 $select_manager = $this->get_select_manager();
	 	 $includes       = $select_manager->get_includes(); //still refers to classic includes.

	 	 #get implicit includes.
	 	 $auto_includes = $model_instance->get_auto_include();

	 	 $include_instances = $auto_includes; //array_merge($includes, $auto_includes);
	 	 
	 	 #include authors and modifiers information
	 	 if($this->include_authors && $model_instance->get_auto_cm()){
	 	 	 $include_instances[] = new One2One(pdao: '', fdao: AUTH_MODEL_CLASS, field: 'author', pk: 'added_by', fk: 'user_id', isnav: true, multiple: false, eager: 1);
	 	 	 $include_instances[] = new One2One(pdao: '', fdao: AUTH_MODEL_CLASS, field: 'modifier', pk: 'modified_by', fk: 'user_id', isnav: true, multiple: false, eager: 1);
	 	 }

	 	 #include tenant information
	 	 if($this->include_tenant){
	 	 	 $include_instances[] = new One2One(pdao: '', fdao: TENANT_MODEL_CLASS, field: 'tenant', pk: 'tenant_id', fk: 'tenant_id', isnav: true, multiple: false, eager: 1);
	 	 }

	 	 $include_rows = [];
 	 	 foreach($include_instances as $ins){
 	 	 	 $fdao         = $ins->get_fdao();
 	 	 	 $pkey         = $ins->get_pk();
 		 	 $fkey         = $ins->get_fk();
 		 	 $pkey_values  = array_column($rows, $pkey);
 		 	 $results      = $fdao::db()->limit(records: 1000, page: 1)->where("{$fkey}__in", $pkey_values)->all();
 		 	 $formatted_results = [];
 		 	 foreach($results as $r){
                 $formatted_results[$r->$fkey][] = $r;
             }
             $include_rows[$ins->get_field()] = ['data' => $formatted_results, 'key' => $pkey, 'multiple' => $ins->get_multiple()];
	 	 }

         $file_configurations = $model_instance->get_file_configurations();
	 	 foreach($rows as $row){
	 	 	 #set includes
	 	 	 if($include_rows){
	 	 	 	 foreach($include_rows as $include_field => $include_info){
	 	 	 	 	$row->$include_field = $include_info['multiple'] ? [] : null;
	 	 	 	 	$primary_key_name  = $include_info['key'];
	 	 	 	 	$primary_key_value = $row->$primary_key_name;
	 	 	 	 	if(array_key_exists($primary_key_value, $include_info['data'])){
	 	 	 	 		 $row->$include_field = $include_info['multiple'] ? $include_info['data'][$primary_key_value] 
	 	 	 	 		 : (count($include_info['data'][$primary_key_value]) > 0 ? $include_info['data'][$primary_key_value][0] : null);
	 	 	 	 	}
	 	 	 	 }
	 	     }
	 	 	 
	 	 	 #format date and timestamps.
	     	 if($model_instance->get_auto_cmdt()){
	     	 	 if(isset($row->date_added)){
				     $row->date_added_display = self::format_date($row->date_added, DATE_ADDED_FORMAT);
				     $row->datetime_added_display = self::format_date($row->date_added, DATETIME_DISPLAY_FORMAT);
				 }
				 if(isset($row->last_modified)){
				     $row->last_modified_display = self::format_date($row->last_modified, DATE_ADDED_FORMAT);
				     $row->datetime_last_modified_display = self::format_date($row->last_modified, DATETIME_DISPLAY_FORMAT);
				 }
	     	 }

             #include file urls
	     	 if(count($file_configurations) > 0){
	     	 	 foreach($file_configurations as $file_key => $file_config){
	     	 		 //get the file path
		     	     $folder_path = $file_configurations[$file_key]['path'] ?? "";
			         if($folder_path && method_exists($model_instance, $folder_path)){
		 				 $folder_path = $model_instance->$folder_path($row);
		 			 }

		 			 //echo "$folder_path\n";
		 			 //echo str_replace(DOCUMENT_ROOT, ROOT_DOMAIN, $folder_path)."\n";
		 			 //echo "---------\n";

		 			 if( isset($row->$file_key) && $row->$file_key){
		 			 	if(HIDDEN_MEDIA_FOLDER){
		 			 		$show_file = $file_configurations[$file_key]['show_file'] ?? "";
		 			 		if($show_file && method_exists($model_instance, $show_file)){
				 				 $show_file = $model_instance->$show_file($row);
				 			}
				 			$folder_path = $this->encrypt($folder_path, $row->$file_key);
				 			$file_url = $this->add_url_parameter($show_file, ['file', 'xyz'], [$row->$file_key, $folder_path]);
		 			 	}else{
		 			 		$file_url = str_replace(DOCUMENT_ROOT, ROOT_DOMAIN, $folder_path).$row->$file_key;
		 			 	}
		 			 	$row->$file_key = $file_url;
		 			 }else{
		 			 	 $default = $file_configurations[$file_key]['default'] ?? "";
		 			 	 if($default && method_exists($model_instance, $default)){
		 			 	 	 $row->$file_key = $model_instance->$default($row);
		 			     }
		 			 }
		     	 }
	     	 }

 	 	 }
	 	 return $rows;
	 }

	 public function total(){
	 	 /*setup a select command*/
	 	 $this->crud_command = new TotalCommand(
	 	 	 new TotalOperation($this->get_connection()),
	 	 	 where_clause:  $this->fmanager->get_where_clause($this->get_context_tracker()),
	 	 	 join_clause:   $this->get_join_clause(),
	 	 	 limit_clause:  $this->get_limit_clause(),
	 	 	 order_clause:  $this->get_order_clause(),
	 	 	 table_name:    $this->get_context_tracker()->find_table_name(0),
	 	 	 table_aliase:  $this->get_context_tracker()->find_table_aliase(0),
	 	 	 database_name: $this->get_context_tracker()->find_database_name(0)
	 	 );
	 	 /*execute command and return response*/
	 	 return $this->crud_command->execute();
	 }

	 //includes
	 public function with(string $field){
	 	 #Make sure this field is a navigation or foreign key field
     	 $table = $this->get_context_tracker()->find_table_name(0);
     	 #get the model associated with this table
     	 $model = $this->get_model($table);
     	 #get include field
     	 $include_field = $model->is_include($field);
     	 if(!$include_field){
     	 	throw new \Exception("{$field} This is not an includable field!");
     	 }
     	 $select_manager = $this->get_select_manager();
	 	 $select_manager->add_include($include_field);
	 	 return $this;
	 }

	 public function with_authors(){
	 	 $this->include_authors = true;
	 	 return $this;
	 }

	 public function with_tenant(){
	 	 $this->include_tenant = true;
	 	 return $this;
	 }

     //inserts
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
     	 $files = array_values($this->_file_data);
     	 foreach($files as $f){
     	 	/*save original files*/
	         $crop_dimensions   = $f->config['crop_dimensions'] ?? null;
	         $resize_dimensions = $f->config['resize_dimensions'] ?? null;
	         $folder_path       = $f->config['path'] ?? "";
	         if(is_array($f->file['name'])){
	             foreach($f->file['name'] as $n_index1 => $n){
	                 $this->commit_file_todisk($folder_path, $f->file['name'][$n_index1], $f->file['tmp_name'][$n_index1], $crop_dimensions, $resize_dimensions);
	             }
	         }else{
	             $this->commit_file_todisk($folder_path, $f->file['name'], $f->file['tmp_name'], $crop_dimensions, $resize_dimensions);
	         }
     	 }
	 }

     private function save_changes(){
	 	 /*setup an insert command*/
	 	 $this->crud_command = new InsertCommand(
	 	 	 new InsertOperation($this->get_connection()),
	 	 	 prmkeytype:   $this->_insert_data_container["prmkeytype"],
	 	 	 prmkeyname:   $this->_insert_data_container["prmkeyname"],
	 	 	 prmkeyvalues: $this->_insert_data_container["prmkeyvalues"],
	 	 	 fields:       array_keys($this->_insert_data_container["data"][0]),
	 	 	 data:         $this->_insert_data_container["data"],
	 	 	 table:        $this->get_context_tracker()->find_table_name(0),
	 	 	 database:     $this->get_context_tracker()->find_database_name(0)
	 	 );
	 	 /*execute command and return response*/
	 	 $result = $this->crud_command->execute();
	 	 if($result && $this->_file_data){
	 	 	 $this->auto_save_files();
	 	 }
	 	 return $result;
     }
 
     public function add(array $data, bool $allow_duplicates = true, array $unique_fields = []){
     	 /*get the name of the current table being manipulated*/
     	 $table = $this->get_context_tracker()->find_table_name(0);
     	 /*get the model associated with this table*/
     	 $model = $this->get_model($table);
     	 [$clean_data, $file_data] = $model->prepare_insert_data($data);
     	 
     	 $this->_insert_data_container["prmkeyname"] = $model->get_pk_name();
     	 $this->_insert_data_container["prmkeyvalues"][] = $clean_data[$model->get_pk_name()];
     	 $this->_insert_data_container["prmkeytype"] = $model->get_pk_type();
     	 $this->_file_data = array_merge($this->_file_data, $file_data);
     	 $this->_insert_data_container["data"][] = $clean_data;
     	 return $this;
     }

     public function add_multiple(array $data, bool $allow_duplicates = true, array $unique_fields = []){
     	foreach($data as $dk => $dv){
     		$this->add($dv, $allow_duplicates, $unique_fields);
     	}
     	return $this;
     }

     public function save(bool $multiple = false){
     	 $saved_data = $this->save_changes();
     	 return $multiple ? $saved_data : $saved_data[0];
     }

     // deletes
     private function soft_delete(){
     	 /*setup an update command*/
	 	 $this->crud_command = new UpdateCommand(
	 	 	 new UpdateOperation($this->get_connection()),
	 	 	 where_clause:  $this->fmanager->get_where_clause($this->get_context_tracker()),
	 	 	 table_name:    $this->get_context_tracker()->find_table_name(0),
	 	 	 database_name: $this->get_context_tracker()->find_database_name(0),
	 	 	 fields:        ["deleted"],
	 	 	 values:        [1]
	 	 );
	 	 /*execute command and return response*/
	 	 return $this->crud_command->execute();
     }

     private function hard_delete(){
     	/*setup a delete command*/
	 	 $this->crud_command = new DeleteCommand(
	 	 	 new DeleteOperation($this->get_connection()),
	 	 	 where_clause:  $this->fmanager->get_where_clause($this->get_context_tracker()),
	 	 	 table_name:    $this->get_context_tracker()->find_table_name(0),
	 	 	 database_name: $this->get_context_tracker()->find_database_name(0)
	 	 );
	 	 /*execute command and return response*/
	 	 return $this->crud_command->execute();
     }

     public function delete(bool $permanently = false){
     	 return $permanently ? $this->hard_delete() : $this->soft_delete();
     }

     public function ignore_soft_delete(){
	 	 $this->_ignore_soft_delete = true;
	 	 return $this;
	 }

     //updates
     public function set(array $data){
     	 /*get the name of the current table being manipulated*/
     	 $table = $this->get_context_tracker()->find_table_name(0);
     	 /*get the model associated with this table*/
     	 $model = $this->get_model($table);
     	 [$clean_data, $file_data] = $model->prepare_update_data($data);
     	 $this->_update_data_container["data"] = array_merge($this->_update_data_container["data"], $clean_data);
     	 $this->_file_data = array_merge($this->_file_data, $file_data);
     	 return $this;
     }

     public function set_multiple(array $data){
     	 foreach($data as $dk => $dv){
     		$this->set($dv);
     	 }
     	 return $this;
     }

     public function update(bool $partial = false){
     	 /*setup an update command*/
	 	 $this->crud_command = new UpdateCommand(
	 	 	 new UpdateOperation($this->get_connection()),
	 	 	 where_clause:  $this->fmanager->get_where_clause($this->get_context_tracker()),
	 	 	 table_name:    $this->get_context_tracker()->find_table_name(0),
	 	 	 database_name: $this->get_context_tracker()->find_database_name(0),
	 	 	 fields:        array_keys($this->_update_data_container["data"]),
	 	 	 values:        array_values($this->_update_data_container["data"])
	 	 );

	 	 /*execute command and return response*/
	 	 $result = $this->crud_command->execute();
	 	 if($result && $this->_file_data){
	 	 	 $this->auto_save_files();
	 	 }
	 	 return $result;
     }

     /**
      * Run raw sql statements against the db
      * */
     public function run($sql, $operation, $data = null, $multiple = true){
     	 /*setup a run command*/
	 	 $this->crud_command = new RunCommand(
	 	 	 new RunOperation($this->get_connection()),
	 	 	 sql:       $sql,
	 	 	 operation: $operation,
	 	 	 data:      $data,
	 	 	 multiple: $multiple,
	 	 );

	 	 /*execute command and return response*/
	 	 return $this->crud_command->execute();
     }

}
?>