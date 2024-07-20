<?php
 namespace SaQle\Dao\Model\Manager;

 use SaQle\Dao\Commands\Crud\{SelectCommand, InsertCommand, DeleteCommand, UpdateCommand, TotalCommand, TableCreateCommand, RunCommand};
 use SaQle\Dao\Operations\Crud\{SelectOperation, InsertOperation, DeleteOperation, UpdateOperation, TotalOperation, TableCreateOperation, RunOperation};
 use SaQle\Dao\Commands\ICommand;
 use SaQle\Dao\Model\Exceptions\NullObjectException;
 use SaQle\Dao\Model\IModel;
 use function SaQle\Exceptions\{modelnotfoundexception};
 use SaQle\Dao\Model\Model;
 use SaQle\Image\Image;
 use SaQle\Dao\Field\Attributes\NavigationKey;
 use SaQle\Commons\{DateUtils, UrlUtils, StringUtils};
 use SaQle\Services\Container\ContainerService;
 use SaQle\Services\Container\Cf;

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

     	 #get model references
     	 $model_references = $this->get_model_references();

     	 #if the from/to field is not provided, assume it is the primary key of the base table
     	 $dao_class    = $model_references[$table];
		 $dao_instance = new $dao_class();
		 $dao_instance->set_request($this->request);
		 $model        = new Model($dao_instance);
		 $primary_key_name = $model->get_primary_key_name();

     	 $from = $from ?: $primary_key_name;
     	 $to   = $to   ?: $primary_key_name;
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
	 	 $model        = $this->get_model($table_name);
	 	 $dao_class    = $model->get_dao()::class;

	 	 #apply a default limit if one was not provided
	 	 $limit_clause = $this->get_limit_clause();
	 	 if(!$limit_clause){
	 	 	 $this->limit();
	 	 	 $limit_clause = $this->get_limit_clause();
	 	 }

	 	 #apply a soft delete filter if the current dao has a soft delete attribute
	 	 if($model->has_softdeletefields() && !$this->_ignore_soft_delete){
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
	 	 	 daoclass:      $dao_class
	 	 );

	 	 #execute command and return response
	 	 $rows = $this->crud_command->execute();

         #get explicit includes
	 	 $select_manager = $this->get_select_manager();
	 	 $includes       = $select_manager->get_includes();

	 	 #get implicit includes.
     	 $navigation_fields = $model->get_navigation_fields();
     	 $foreign_keys      = $model->get_foreign_keys();
     	 $auto_includes     = [];
     	 foreach(array_merge($navigation_fields, $foreign_keys) as $if){
     	 	 $attribs = $if->get_raw_attributes();
     	 	 foreach($attribs as $attr){
     	 	 	$ins = $attr->newInstance();
     	 	 	if($ins->get_include()){
     	 	 		$auto_includes[] = $attr;
     	 	 	}
     	 	 }
     	 }
         $includes          = array_merge($includes, $auto_includes);
         $include_instances = [];
     	 foreach($includes as $in){
 	 	 	 $include_instances[] = $in->newInstance();
 	 	 }

	 	 #include authors and modifiers information
	 	 if($this->include_authors && $model->has_creatormodifierfields()){
	 	 	 $include_instances[] = new NavigationKey(pdao: '', fdao: AUTH_MODEL_CLASS, multiple: false, field: 'author',   include: true, pfkeys: 'added_by=>user_id');
	 	 	 $include_instances[] = new NavigationKey(pdao: '', fdao: AUTH_MODEL_CLASS, multiple: false, field: 'modifier', include: true, pfkeys: 'modified_by=>user_id');
	 	 }

	 	 #include tenant information
	 	 if($this->include_tenant){
	 	 	 $include_instances[] = new NavigationKey(pdao: '', fdao: TENANT_MODEL_CLASS, multiple: false, field: 'tenant',   include: true, pfkeys: 'tenant_id=>tenant_id');
	 	 }

	 	 $include_rows = [];
	 	 if($include_instances){
	 	 	 $dbcontext_class = $this->get_dbcontext_class();
             $dbcontext = Cf::create(ContainerService::class)->createDbContext($dbcontext_class);
	 	 	 foreach($include_instances as $ins){
	 	 	 	 $current_table = $dbcontext->get_dao_table_name($ins->get_fdao());
	 	 	 	 if($current_table){
     	 		 	 $pfkeys       = explode("=>", $ins->get_pfkeys());
		 		 	 $pkey         = $pfkeys[0];
		 		 	 $fkey         = $pfkeys[1];
		 		 	 $pkey_values  = array_column($rows, $pkey);
		 		 	 $results      = $dbcontext->get($current_table)->limit(records: 1000, page: 1)->where("{$fkey}__in", $pkey_values)->all();
		 		 	 $formatted_results = [];
		 		 	 foreach($results as $r){
		                 $formatted_results[$r->$fkey][] = $r;
		             }
		             $include_rows[$ins->get_field()] = ['data' => $formatted_results, 'key' => $pkey, 'multiple' => $ins->get_multiple()];
     	 		 } 
		 	 }
	 	 }

         $file_configurations = $model->get_file_configurations();
         $dao_instance = $model->get_dao();
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
	     	 if($model->has_createmodifydatetimefields()){
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
			         if($folder_path && method_exists($dao_instance, $folder_path)){
		 				 $folder_path = $dao_instance->$folder_path($row);
		 			 }

		 			 //echo "$folder_path\n";
		 			 //echo str_replace(DOCUMENT_ROOT, ROOT_DOMAIN, $folder_path)."\n";
		 			 //echo "---------\n";

		 			 if( isset($row->$file_key) && $row->$file_key){
		 			 	if(HIDDEN_MEDIA_FOLDER){
		 			 		$show_file = $file_configurations[$file_key]['show_file'] ?? "";
		 			 		if($show_file && method_exists($dao_instance, $show_file)){
				 				 $show_file = $dao_instance->$show_file($row);
				 			}
				 			$folder_path = $this->encrypt($folder_path, $row->$file_key);
				 			$file_url = $this->add_url_parameter($show_file, ['file', 'xyz'], [$row->$file_key, $folder_path]);
		 			 	}else{
		 			 		$file_url = str_replace(DOCUMENT_ROOT, ROOT_DOMAIN, $folder_path).$row->$file_key;
		 			 	}
		 			 	$row->$file_key = $file_url;
		 			 }else{
		 			 	 $default = $file_configurations[$file_key]['default'] ?? "";
		 			 	 if($default && method_exists($dao_instance, $default)){
		 			 	 	 $row->$file_key = $dao_instance->$default($row);
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
	 public function include(string $field){
	 	 #Make sure this field is a navigation or foreign key field
     	 $table = $this->get_context_tracker()->find_table_name(0);
     	 #throw a model not found exception if this table is not registered
     	 modelnotfoundexception($table, $this->_model_references, $this->get_context_options()->get_name());
     	 #get the model associated with this table
     	 $model = $this->get_model($table);
     	 #get include field
     	 $include_field = $model->get_include_field($field);
     	 if(!$include_field){
     	 	throw new \Exception("{$field} This is not an includable field!");
     	 }
     	 $attribites = $include_field->get_raw_attributes();
     	 if($attribites){
     	 	 $select_manager = $this->get_select_manager();
	 	     $select_manager->add_include($attribites[0]);
     	 }
	 	 return $this;
	 }

	 public function include_authors(){
	 	 $this->include_authors = true;
	 	 return $this;
	 }

	 public function include_tenant(){
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
         /*get the dao associatedwith model*/
     	 $dao = $model->get_dao();
     	 [$clean_data, $file_data] = $dao->prepare_insert_data($data);
     	 $this->_insert_data_container["prmkeyname"] = $dao->get_pk_name();
     	 $this->_insert_data_container["prmkeyvalues"][] = $clean_data[$dao->get_pk_name()];
     	 $this->_insert_data_container["prmkeytype"] = $dao->get_pk_type();
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
         /*get the dao associated with model*/
     	 $dao = $model->get_dao();
     	 [$clean_data, $file_data] = $dao->prepare_update_data($data);
     	 $this->_update_data_container["data"] = array_merge($this->_update_data_container["data"], $clean_data);
     	 $this->_file_data = array_merge($this->_file_data, $file_data);
     	 return $this;
     }

     public function set_multiple(array $data){
     	 foreach($data as $dk => $dv){
     		$this->set($dv, $partial);
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
      * Create a new database table.
      * */
     public function create_table(){
     	 /*get the name of the current table being manipulated*/
     	 $table  = $this->get_context_tracker()->find_table_name(0);
     	 /*get the model associated with this table*/
     	 $model  = $this->get_model($table);
     	 $fields = $model->get_fields();

     	 $field_properties = [];
     	 foreach($fields as $f){
     	 	 $validation       = $f->get_validation_attributes();
     	 	 $is_numeric       = $f->is_numeric();
     	 	 $is_primary       = $f->is_primary_key();
     	 	 $length           = $validation['length'] ?? ($is_numeric ? 11 : 255);
     	 	 $is_required      = $validation['is_required'] ?? false;

     	 	 $val              = $f->get_value();

     	 	 $field_line       = [$f->get_name()];
     	 	 $field_line[]     = $is_numeric ? 'INT('.$length.')' : 'VARCHAR('.$length.')';
     	 	 if($is_primary){
     	 	 	 $field_line[] = $is_numeric ? 'AUTO_INCREMENT PRIMARY KEY' : 'PRIMARY KEY';
     	 	 }

     	 	 $field_line[]     = $is_required || $is_primary ? 'NOT NULL' : 'NULL';
     	 	 $field_line[]     = $val ? 'DEFAULT '.$val : '';

 	 	 	 $field_properties[] = implode(" ", $field_line);
     	 }

     	 /*add CreatorModifierFields, CreateModifyDateTimeFields and SoftDeleteFields if these attributes exist on data access object*/
     	 if($model->has_creatormodifierfields()){
     	 	 $field_properties[] = "added_by VARCHAR(100) NOT NULL";
     	 	 $field_properties[] = "modified_by VARCHAR(100) NOT NULL";
     	 }
     	 if($model->has_createmodifydatetimefields()){
     	 	 $field_properties[] = "date_added BIGINT(20) NOT NULL";
     	 	 $field_properties[] = "last_modified BIGINT(20) NOT NULL";
     	 }
     	 if($model->has_softdeletefields()){
     	 	 $field_properties[] = "deleted TINYINT(1) NOT NULL";
     	 }

     	 /*setup a create command*/
	 	 $this->crud_command = new TableCreateCommand(
	 	 	 new TableCreateOperation($this->get_connection()),
	 	 	 table:  $table,
	 	 	 fields: implode(", ", $field_properties)
	 	 );

	 	 /*execute command and return response*/
	 	 return $this->crud_command->execute();
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