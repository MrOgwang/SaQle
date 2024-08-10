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
 use SaQle\Dao\Model\Manager\Handlers\{PathsToUrls, EagerLoadAssign, TypeCast, FormatCmdt};
 use SaQle\Core\Chain\{Chain, DefaultHandler};
 use SaQle\Dao\Model\Manager\Trackers\EagerTracker;

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

	 	 /**
	 	  * Activate eager loading tracker if it hasn't been activated.
	 	  * */

	 	 $include_rows = [];
 	 	 foreach($include_instances as $ins){
 	 	 	 //print_r($ins);
 	 	 	 $fdao         = $ins->get_fdao();
 	 	 	 if(!$tracker::is_loaded($fdao)){
 	 	 	 	 $fdaom        = $fdao::get_associated_model_class();
	 	 	 	 $pkey         = $ins->get_pk();
	 		 	 $fkey         = $ins->get_fk();
	 		 	 $pkey_values  = array_unique(array_column($rows, $pkey));
	 		 	 $results      = $fdaom::db()->limit(records: 1000, page: 1)->where("{$fkey}__in", $pkey_values)->eager_load(tomodel: $tomodel);
	 		 	 $formatted_results = [];
	 		 	 foreach($results as $r){
	                 $formatted_results[$r->$fkey][] = $r;
	             }
	             $include_rows[$ins->get_field()] = ['data' => $formatted_results, 'key' => $pkey, 'multiple' => $ins->get_multiple()];
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

	     if($chain->is_active()){
	     	 $newrows = [];
	     	 foreach($rows as $row){
	     	 	$newrows[] = $chain->apply($row);
	     	 }
	     	 return $newrows;
	     }

	     if(!$tracker_active){
	     	// $tracker::reset();
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

	 public function with(string $field){
	 	 [$field, $model] = $this->check_with($field);
     	 $select_manager = $this->get_select_manager();
	 	 $select_manager->add_include($field);
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

     private function save_changes(bool $tomodel = false){
     	 if(!$this->_insert_data_container["data"]){
     	 	 throw new \Exception("Save attempt on an empty data container!");
     	 	 return null;
     	 }

     	 /*
     	 $proceed = false;
     	 if($this->_operation_status['is_duplicate'] === false || 
     	 ($this->_operation_status['is_duplicate'] === true && $this->_operation_status['duplicate_action'] === 'IGNORE_DUPLICATE')){
     	 	 $proceed = true;
     	 }

     	 if($this->_operation_status['is_duplicate'] === true 
     	 	&& $this->_operation_status['duplicate_action'] === 'BYPASS_DUPLICATE'
     	    && count($this->_insert_data_container["data"]) > 1){
     	 	 $proceed = true;
     	 }


     	 $duplicate_actions = [, '', 'ABORT_WITHOUT_ERROR', 'ABORT_WITH_ERROR', 'RETURN_EXISTING', 'UPDATE_ON_DUPLICATE'];*/


     	 #acquire the table being processed
	 	 $table_name   = $this->get_context_tracker()->find_table_name(0);
	 	 #acquire the dao model.
	 	 $model_instance = $this->get_model($table_name);
	 	 $ass_model_class = $model_instance->get_associated_model_class();

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
	 	 if($result){
	 	 	 if($this->_file_data){
	 	 	 	 $this->auto_save_files();
	 	 	 }
	 	 	 if($tomodel){
	 	 	 	$hydrated = [];
	 	 	 	foreach($result as $r){
	 	 	 		 $hydrated[] = new $ass_model_class(...(array)$r);
	 	 	 	}
	 	 	 	return $hydrated;
	 	 	 }
	 	 }

	 	 return $result;
     }
 
     public function add(array $data, bool $allow_duplicates = true, array $unique_fields = []){
     	 /*get the name of the current table being manipulated*/
     	 $table = $this->get_context_tracker()->find_table_name(0);
     	 /*get the model associated with this table*/
     	 $model = $this->get_model($table);
     	 [$clean_data, $file_data, $is_duplicate, $action_on_duplicate] = $model->prepare_insert_data($data);

     	 if($is_duplicate){
     	 	 $this->_operation_status['is_duplicate'] = true;
     	 	 if(!array_key_exists('duplicate_entries', $this->_operation_status)){
     	 	 	 $this->_operation_status['duplicate_entries'] = [];
     	 	 }
     	 	 $this->_operation_status['duplicate_entries'][] = $is_duplicate;
     	 	 $this->_operation_status['duplicate_action'] = $action_on_duplicate;
     	 }

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

     public function save(bool $tomodel = false){
     	 $saved_data = $this->save_changes(tomodel: $tomodel);
     	 if(!$saved_data)
     	 	return null;

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
     public function set(array $data){
     	 /*get the name of the current table being manipulated*/
     	 $table = $this->get_context_tracker()->find_table_name(0);
     	 /*get the model associated with this table*/
     	 $model = $this->get_model($table);
     	 [$clean_data, $file_data, $is_duplicate, $action_on_duplicate] = $model->prepare_update_data($data);

     	 if($is_duplicate){
     	 	 $this->_operation_status['is_duplicate'] = true;
     	 	 if(!array_key_exists('duplicate_entries', $this->_operation_status)){
     	 	 	 $this->_operation_status['duplicate_entries'] = [];
     	 	 }
     	 	 $this->_operation_status['duplicate_entries'][] = $is_duplicate;
     	 	 $this->_operation_status['duplicate_action'] = $action_on_duplicate;
     	 }

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
     	 if($this->_is_operation_aborted || !$this->_update_data_container["data"]){
     	 	 return null;
     	 }

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