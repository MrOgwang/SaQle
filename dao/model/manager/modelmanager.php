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
 use SaQle\Dao\Field\Relations\{One2One, Many2Many};
 use SaQle\Dao\Model\Manager\Handlers\{PathsToUrls, EagerLoadAssign, TypeCast, FormatCmdt};
 use SaQle\Core\Chain\{Chain, DefaultHandler};
 use SaQle\Dao\Model\Manager\Trackers\EagerTracker;
 use SaQle\Core\Assert\Assert;

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
	 private function eager_load(bool $tomodel = false){
	 	 return $this->get(tomodel: $tomodel, tracker_active: true);
	 }

	 /*
	 * return all the rows found.
	 */
	 public function all(bool $tomodel = false){
	 	 return $this->get($tomodel);
	 }

	 /*
	 * return the first row if its available otherwise throw an error
	 */
	 public function first(bool $tomodel = false){
	 	 $response = $this->get($tomodel);
	 	 if(!$response){
	 	 	$table = $this->get_context_tracker()->find_table_name(0);
	 	 	throw new NullObjectException(table: $table);
	 	 }
	 	 return $response[0];
	 }

	 /*
	    return the first row if its available otherwise return null
	 */
	 public function first_or_default(bool $tomodel = false){
	 	 $response = $this->get($tomodel);
	 	 return $response ? $response[0] : null;
	 }

     /*
     * reteurn the last row if its available otherwise throw an error
     */
	 public function last(bool $tomodel = false){
	 	 $response = $this->get($tomodel);
	 	 if(!$response){
	 	 	throw NullObjectException(table: $this->get_context_tracker()->find_table_name(0));
	 	 }
	 	 return $response[count($response) - 1];
	 }

	 /*
	 * return the last row if its available otherwise return null
	 */
	 public function last_or_default(bool $tomodel = false){
	 	 $response = $this->get($tomodel);
	 	 return $response ? $response[count($response) - 1] : null;
	 }

	 private function get(bool $tomodel = false, bool $tracker_active = false){
	 	 #acquire the table being processed
	 	 $table_name   = $this->get_context_tracker()->find_table_name(0);
	 	 #acquire the dao model.
	 	 $model_instance = $this->get_model($table_name);
	 	 $model_class    = $model_instance::class;
	 	 $ass_model_class = $model_instance->get_associated_model_class();

	 	 /**
	 	  * activate tracker and add current model to it.
	 	  * */
	 	 $tracker = EagerTracker::activate();
	 	 if(!$tracker_active){
	 	 	 $tracker::reset();
	 	 }
	 	 $tracker->add_model($model_class);

	 	 //print_r($tracker::get_loaded_models());

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
	 	 	 tomodel:         $tomodel,
	 	 	 daoclass:      $model_class
	 	 );

	 	 #execute command and return response
	 	 $rows = $this->crud_command->execute();

         #get explicit includes
	 	 $select_manager = $this->get_select_manager();
	 	 $includes       = $select_manager->get_includes();

	 	 #get implicit includes.
	 	 $auto_includes = $model_instance->get_auto_include();

	 	 $include_instances = array_merge($includes, $auto_includes);

	 	 $include_rows = [];
 	 	 foreach($include_instances as $ins){
 	 	 	 $fdao         = $ins->get_fdao();
 	 	 	 if(!$tracker::is_loaded($fdao)){
 	 	 	 	 if($ins instanceof Many2Many){
 	 	 	 	 	 $ofdao = $fdao;
 	 	 	 	 	 $ofdaom = $ofdao::get_associated_model_class();
 	 	 	 	 	 $collection_class = $ofdaom::get_collection_class();
 	 	 	 	 	 [$table_name, $schema, $ctx] = $ins->get_through_model_schema();
 	 	 	 	 	 $fdao = $schema;
 	 	 	 	 	 $fdaom        = $fdao::get_associated_model_class();
		 	 	 	 $pkey         = $ins->get_pk();
		 		 	 $fkey         = $ins->get_fk();
		 		 	 $pkey_values  = array_unique(array_column($rows, $pkey));
		 		 	 $with_field   = $schema::get_include_field($ins->get_pdao());
		 		 	 $with_field2  = $schema::get_include_field($ins->get_fdao());
		 		 	 $results      = $fdaom::db2($table_name, $schema, $ctx)->with($with_field)
		 		 	 ->limit(records: 1000, page: 1)->where("{$pkey}__in", $pkey_values)->eager_load(tomodel: $tomodel);

		 		 	 $formatted_results = [];
		 		 	 foreach($results as $r){
		 		 	 	 if(!array_key_exists($r->$with_field2, $formatted_results)){
		 		 	 	 	 $formatted_results[$r->$with_field2] = new $collection_class([]);
		 		 	 	 }
		 		 	 	 $formatted_results[$r->$with_field2]->add($r->$with_field);
		             }
		 		 	 
 	 	 	 	 	 $include_rows[$ins->get_field()] = ['collection_class' => $collection_class, 'data' => $formatted_results, 'key' => $pkey, 'multiple' => $ins->get_multiple()];
 	 	 	 	 }else{
 	 	 	 	 	 $fdaom        = $fdao::get_associated_model_class();
		 	 	 	 $pkey         = $ins->get_pk();
		 		 	 $fkey         = $ins->get_fk();
		 		 	 $pkey_values  = array_unique(array_column($rows, $pkey));
		 		 	 $results      = $fdaom::db()->limit(records: 1000, page: 1)->where("{$fkey}__in", $pkey_values)->eager_load(tomodel: $tomodel);
		 		 	 //print_r($results);
		 		 	 $formatted_results = [];
		 		 	 foreach($results as $r){
		                 $formatted_results[$r->$fkey][] = $r;
		             }
		             $include_rows[$ins->get_field()] = ['data' => $formatted_results, 'key' => $pkey, 'multiple' => $ins->get_multiple()];
 	 	 	 	 }
 	 	 	 } 
	 	 } 

	 	 $chain = new Chain();
	 	 $chain->add(new DefaultHandler());

	 	 /**
	 	  * Assign eager loaded objects
	 	  * */
	 	 if($include_rows){
	 	 	 $chain->add(new EagerLoadAssign(data: $include_rows));
	 	 }
	 	 
	 	 /**
	 	  * Format date and timestamps to human readable forms
	 	  * */
	     if($model_instance->get_auto_cmdt() && $model_instance->get_format_cmdt() && $model_instance->get_cmdt_type() === 'PHPTIMESTAMP'){
	     	 $createdat_name = $model_instance->get_created_at_field_name();
	     	 $modifiedat_name = $model_instance->get_modified_at_field_name();
	     	 $chain->add(new FormatCmdt(cat_name: $createdat_name, mat_name: $modifiedat_name));
	     }

         /**
          * Format file paths to urls
          * */
	     $file_configurations = $model_instance->get_file_configurations();
	     if(count($file_configurations) > 0){
	     	 $chain->add(new PathsToUrls(model: $model_instance, config: $file_configurations));
	     }

	     /**
	      * Cast the row data to value object type
	      * */
	     if($tomodel){
	     	 $chain->add(new TypeCast(type: $ass_model_class));
	     }

	     /**
	      * Process the data through the chain.
	      * */
	     if($chain->is_active()){
	     	 $processed = [];
	     	 foreach($rows as $row){
	     	 	 $processed[] = $chain->apply($row);
	     	 }
	     	 $rows = $processed;
	     }

	     if(!$tracker_active){
	     	// $tracker::reset();
	     }

	     /**
     	  * If to model is on, return a typed model collection instead
     	  * of a simple array
     	  * */
     	 if($tomodel){
     	 	$collection_class = $ass_model_class::get_collection_class();
     	 	return new $collection_class($rows);
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
	 private function check_with(string $field){
	 	 #Make sure this field is a navigation or foreign key field
     	 $table = $this->get_context_tracker()->find_table_name(0);
     	 #get the model associated with this table
     	 $model = $this->get_model($table);
     	 #get include field
     	 $include_field = $model->is_include($field);

     	 if(!$include_field){
     	 	throw new \Exception("{$field} This is not an includable field!");
     	 }

     	 return [$include_field, $model];
	 }

	 public function with(array|string $field){
	 	 $fields = is_array($field) ? $field : [$field];
	 	 $select_manager = $this->get_select_manager();
	 	 foreach($fields as $wf){
	 	 	 [$field, $model] = $this->check_with($wf);
	 	 	 $select_manager->add_include($field);
	 	 }
	 	 return $this;
	 }

     /**
      * Short cut to eager load author and modifier
      * user information
      * */
	 public function with_authors(){
	 	 [$a_field, $model] = $this->check_with("author");
	 	 [$m_field] = $this->check_with("modifier");

	 	 if($model->get_auto_cm()){
	 	 	 $select_manager = $this->get_select_manager();
	 	     $select_manager->add_include($a_field);
	 	     $select_manager->add_include($m_field);
	 	 }
	 	 return $this;
	 }

     /**
      * Short cut to eager load tenant information.
      * */
	 public function with_tenant(){
	 	 [$field, $model] = $this->check_with("tenant");
	 	 if($model->is_multitenant()){
	 	 	 $select_manager = $this->get_select_manager();
	 	     $select_manager->add_include($field);
	 	 }
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

     private function assert_duplicates(){
     	 if(array_key_exists("is_duplicate", $this->_operation_status)){
     	 	 switch($this->_operation_status['duplicate_action']){
	     	 	 case 'IGNORE_DUPLICATE':
	     	 	     /**
	     	 	      * Do nothing. This tells the modelmanager to add records despite the duplicates.
	     	 	      * */
	     	 	 break;
	     	 	 case 'BYPASS_DUPLICATE':
	     	 	     /**
	     	 	      * Remove duplicate data from the data container, duplicate entries record and file data if applicable..
	     	 	      * */
	     	 	     $duplicate_keys = array_keys($this->_operation_status['duplicate_entries']);
		     	 	 foreach($duplicate_keys as $key){
		     	 	 	 Assert::keyExists($this->_insert_data_container["data"], $key);
		     	 	 	 Assert::keyExists($this->_operation_status['duplicate_entries'], $key);

		     	 	 	 unset($this->_insert_data_container["data"][$key]);
		     	 	 	 unset($this->_operation_status['duplicate_entries'][$key]);

		     	 	 	 if(isset($this->_file_data[$key])){
		     	 	 	 	unset($this->_file_data[$key]);
		     	 	 	 }

		     	 	 	 if(isset($this->_insert_data_container["prmkeyvalues"][$key])){
		     	 	 	 	unset($this->_insert_data_container["prmkeyvalues"][$key]);
		     	 	 	 }
		     	 	 }

		     	 	 Assert::isNonEmptyMap($this->_insert_data_container["data"], "Save attempt on an empty data container after removing duplicates!");
	     	 	 break;
	     	 	 case 'ABORT_WITHOUT_ERROR':
	     	 	 case 'ABORT_WITH_ERROR':
	     	 	 	 throw new \Exception("Aborting insert operation. Duplicate entries were found in data!");
	     	 	 break;
	     	 	 case 'UPDATE_ON_DUPLICATE':
	     	 	 	 /**
	     	 	 	  * Remove duplicate data from the data container and leave the duplicate entries as they will be 
	     	 	 	  * used to update the existing records after saving the rest.
	     	 	 	  * */
	     	 	 	 $duplicate_keys = array_keys($this->_operation_status['duplicate_entries']);
		     	 	 foreach($duplicate_keys as $key){
		     	 	 	 Assert::keyExists($this->_insert_data_container["data"], $key);
		     	 	 	 Assert::keyExists($this->_operation_status['duplicate_entries'], $key);

		     	 	 	 /**
		     	 	 	  * replace the existing value with the incoming value.
		     	 	 	  * */
		     	 	 	 $this->_operation_status['duplicate_entries'][$key] = $this->_insert_data_container["data"][$key];

		     	 	 	 unset($this->_insert_data_container["data"][$key]);

		     	 	 	 if(isset($this->_insert_data_container["prmkeyvalues"][$key])){
		     	 	 	 	unset($this->_insert_data_container["prmkeyvalues"][$key]);
		     	 	 	 }
		     	 	 }
	     	 	 break;
	     	 	 case 'RETURN_EXISTING':
	     	 	 	 /**
	     	 	 	  * Remove duplicate data from the data container and the files container and leave the duplicate entries records as they will be 
	     	 	 	  * returned as is to the caller.
	     	 	 	  * */
	     	 	 	 $duplicate_keys = array_keys($this->_operation_status['duplicate_entries']);
		     	 	 foreach($duplicate_keys as $key){
		     	 	 	 Assert::keyExists($this->_insert_data_container["data"], $key);
		     	 	 	 Assert::keyExists($this->_operation_status['duplicate_entries'], $key);

		     	 	 	 unset($this->_insert_data_container["data"][$key]);

		     	 	 	 if(isset($this->_file_data[$key])){
		     	 	 	 	unset($this->_file_data[$key]);
		     	 	 	 }

		     	 	 	 if(isset($this->_insert_data_container["prmkeyvalues"][$key])){
		     	 	 	 	unset($this->_insert_data_container["prmkeyvalues"][$key]);
		     	 	 	 }
		     	 	 }
	     	 	 break;
	     	 }
     	 }
     }

     private function build_update_manager($manager, $fields, $together){
     	 $unique_field_keys = array_keys($fields);
         $first_field = array_shift($unique_field_keys);
         $manager->where($first_field."__eq", $fields[$first_field]);
         if($together){
         	 foreach($unique_field_keys as $uf){
         	 	 $manager->where($uf."__eq", $unique_fields[$uf]);
	 	     }
         }else{
         	 foreach($unique_field_keys as $uf){
         	 	 $manager->or_where($uf."__eq", $unique_fields[$uf]);
	 	     }
         }
         return $manager;
     }

     private function save_changes(bool $tomodel = false){
     	 try{
     	 	 #acquire the table being processed
 	         $table_name   = $this->get_context_tracker()->find_table_name(0);
 	         #acquire the dao model.
 	         $model_instance = $this->get_model($table_name);
 	         $ass_model_class = $model_instance->get_associated_model_class();

     	 	 Assert::isNonEmptyMap($this->_insert_data_container["data"], "$ass_model_class: Save attempt on an empty data container!");
     	     $this->assert_duplicates();

     	     #At this point, if there is still data to be saved in the data container, save it.
     	     $results = null;
     	     if($this->_insert_data_container["data"]){
	 	         #setup insert command.
	 	         $this->crud_command = new InsertCommand(
			 	 	 new InsertOperation($this->get_connection()),
			 	 	 prmkeytype:   $this->_insert_data_container["prmkeytype"],
			 	 	 fields:       array_keys(array_values($this->_insert_data_container["data"])[0]),
			 	 	 data:         array_values($this->_insert_data_container["data"]),
			 	 	 table:        $this->get_context_tracker()->find_table_name(0),
			 	 	 database:     $this->get_context_tracker()->find_database_name(0)
			 	 );
			 	 #execute command and return response
			 	 $response = $this->crud_command->execute();
			 	 #save any files in the files container.
			 	 $this->auto_save_files();

                 $primary_key_values = [];
			 	 if($this->_insert_data_container["prmkeytype"] === 'GUID'){
			 	 	 $primary_key_values = array_values($this->_insert_data_container["prmkeyvalues"]);
			 	 }else{
					 for($i = 0; $i < $response->row_count; $i++){
					    $primary_key_values[] = $response->last_insert_id + $i;
					 }
			 	 }

			 	 #fetch all the data just saved.
			 	 if(str_contains($ass_model_class, 'Throughs')){
			 	 	 $man = $ass_model_class::db2($table_name, $model_instance::class, $this->get_dbcontext_class());
			 	 }else{
			 	 	 $man = $ass_model_class::db();
			 	 }
		 	 	 $results = $man->where($this->_insert_data_container["prmkeyname"]."__in", $primary_key_values)
		 	 	 ->eager_load(tomodel: $tomodel);
			 }

			 #now deal with the duplicates.
			 if(!$results){
			 	 if($tomodel){
			 	 	 $collection_class = $ass_model_class::get_collection_class();
			 	 	 $results = new $collection_class([]);
			 	 }else{
			 	 	 $results = [];
			 	 }
			 }

	 	 	 if(array_key_exists("is_duplicate", $this->_operation_status) && $this->_operation_status['duplicate_entries']) {
	 	 	 	 switch($this->_operation_status['duplicate_action']){
	 	 	 	 	 case 'UPDATE_ON_DUPLICATE';
	 	 	 	 	     $unique_together = $this->_operation_status['unique_together'];
					 	 if(str_contains($ass_model_class, 'Throughs')){
					 	 	 $man = $ass_model_class::db2($table_name, $model_instance::class, $this->get_dbcontext_class());
					 	 }else{
					 	 	 $man = $ass_model_class::db();
					 	 }
	 	 	 	 	     foreach($this->_operation_status['duplicate_entries'] as $dk => $dv){
	 	 	 	 	     	 $unique_fields = $this->_operation_status['unique_fields'][$dk];
	 	 	 	 	     	 $man           = $this->build_update_manager($man, $unique_fields, $unique_together);
	 	 	 	 	     	 $updateresp    = $man->set($dv)->update(tomodel: $tomodel, multiple: false, force: true);
	 	 	 	 	     	 if($updateresp){
	 	 	 	 	     	 	$results[]  = $updateresp;
	 	 	 	 	     	 }
	 	 	 	 	     }
	 	 	 	 	 break;
	 	 	 	 	 case "RETURN_EXISTING":
	 	 	 	 	     #Inject the duplicate data into the result.
	 	 	 	 	     if($tomodel){
	 	 	 	 	     	 foreach($this->_operation_status['duplicate_entries'] as $dk => $dv){
	 	 	 	 	     	 	$results->add(new $ass_model_class(...(array)$dv));
	 	 	 	 	         }
	 	 	 	 	     }else{
	 	 	 	 	     	 $results = array_merge($results, array_values($this->_operation_status['duplicate_entries']));
	 	 	 	 	     }
	 	 	 	 	 break;
	 	 	 	 	 default:
	 	 	 	 	     #Do nothing
	 	 	 	 	 break;
	 	 	 	 }
	 	 	 }

			 return $results;
     	 }catch(\Exception $ex){
     	 	throw $ex;
     	 }
     }
 
     public function add(array $data, bool $skip_validation = false){
     	
     	 /*get the name of the current table being manipulated*/
     	 $table = $this->get_context_tracker()->find_table_name(0);
     	 /*get the model associated with this table*/
     	 $model = $this->get_model($table);
     	 [$clean_data, $file_data, $is_duplicate, $action_on_duplicate] = $model->prepare_insert_data($data, $table, $model::class, $this->get_dbcontext_class(), $skip_validation);

     	 $entry_key = spl_object_hash((object)$clean_data);

     	 if($is_duplicate !== false){
     	 	 if(array_key_exists("is_duplicate", $this->_operation_status)){
     	 	 	 $this->_operation_status['duplicate_entries'][$entry_key] = $is_duplicate[0];
     	 	 	 $this->_operation_status['unique_fields'][$entry_key] = $is_duplicate[1];
     	 	 }else{
     	 	 	 $this->_operation_status['is_duplicate'] = true;
     	 	 	 $this->_operation_status['duplicate_action'] = $action_on_duplicate;
     	 	 	 $this->_operation_status['unique_together'] = $is_duplicate[2];

     	 	 	 $this->_operation_status['duplicate_entries'] = [];
     	 	 	 $this->_operation_status['duplicate_entries'][$entry_key] = $is_duplicate[0];

     	 	 	 $this->_operation_status['unique_fields'] = [];
     	 	     $this->_operation_status['unique_fields'][$entry_key] = $is_duplicate[1];
     	 	 }
     	 }

     	 $this->_insert_data_container["prmkeyname"] = $model->get_pk_name();
     	 $this->_insert_data_container["prmkeytype"] = $model->get_pk_type();
     	 if($this->_insert_data_container["prmkeytype"] === 'GUID'){
     	 	 $this->_insert_data_container["prmkeyvalues"][$entry_key] = $clean_data[$model->get_pk_name()];
     	 }
     	 
     	 $this->_file_data[$entry_key] = $file_data;
     	 $this->_insert_data_container["data"][$entry_key] = $clean_data;
     	 return $this;
     }

     public function add_multiple(array $data, bool $skip_validation = false){
     	foreach($data as $dk => $dv){
     		$this->add($dv, $skip_validation);
     	}
     	return $this;
     }

     public function save(bool $tomodel = false){
     	 $saved_data = $this->save_changes(tomodel: $tomodel);
     	 if(!$saved_data)
     	 	return new \Exception("Could not save object");

     	 if(count($this->_insert_data_container["data"]) > 1)
     	 	return $saved_data;

     	 return $saved_data[0];
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

     /**
      * Set the data state of the object that is either being saved or updated
      * at the moment, this only happens when you initialize
      * the manager from a model.
      * */
     public function set_data_state(array $data_state){
     	 $this->_data_state = $data_state;
     	 return $this;
     }

     /**
      * Set collects key => value data reperesenting field names and the new values to update, 
      * Sometimes you need to call set multiple times to have the data you would like to update
      * 
      * @param array $data.
      * */
     public function set(array $data){
     	 $this->_update_data_container["data"] = array_merge($this->_update_data_container["data"], $data);
     	 return $this;
     }

     public function set_multiple(array $data){
     	 foreach($data as $dv){
     		$this->set($dv);
     	 }
     	 return $this;
     }

     public function update(bool $tomodel = false, bool $multiple = false, bool $force = false){
     	 $table = $this->get_context_tracker()->find_table_name(0); //name of current table being manipulated
     	 $model = $this->get_model($table); //the model schema instance for the table.
     	 $ass_model_class = $model->get_associated_model_class();
         #Make sure the update container has some data
     	 Assert::isNonEmptyMap($this->_update_data_container['data'], "$ass_model_class: Update attempt on an empty data container!");
     	 #Clean up the update data, prepare files
     	 [$clean_data, $file_data, $is_duplicate, $action_on_duplicate] = $model->prepare_update_data($this->_update_data_container['data'], $this->_data_state);
     	 #For updates, if there is a duplicate, just abort the update operation.
     	 if($is_duplicate !== false && !$force){
     	 	 throw new \Exception("Aborting update operation! The update operation will lead to duplicate entries in table: {$table}");
     	 }
     	 $this->_file_data[] = $file_data;

     	 //echo "Opened update! version 2\n";

     	 #setup an update command
     	 $where_clause = 
	 	 $this->crud_command = new UpdateCommand(
	 	 	 new UpdateOperation($this->get_connection()),
	 	 	 where_clause:  $this->fmanager->get_where_clause($this->get_context_tracker()),
	 	 	 table_name:    $this->get_context_tracker()->find_table_name(0),
	 	 	 database_name: $this->get_context_tracker()->find_database_name(0),
	 	 	 fields:        array_keys($clean_data),
	 	 	 values:        array_values($clean_data)
	 	 );

	 	 #execute command and return response
	 	 $response = $this->crud_command->execute();
	 	 #response will have a row count of 0 if no rows have been affected
	 	 if($response->row_count > 0){
	 	 	 $this->auto_save_files();
	 	 	 #fetch the data just updated
	 	     $ud = $this->eager_load(tomodel: $tomodel);
	 	     return $multiple ? $ud : $ud[0];
	 	 }

	 	 return false;
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