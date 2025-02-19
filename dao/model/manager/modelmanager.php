<?php
 namespace SaQle\Dao\Model\Manager;

 use SaQle\Dao\Commands\Crud\{SelectCommand, InsertCommand, DeleteCommand, UpdateCommand, TotalCommand, TableCreateCommand, RunCommand, TableDropCommand};
 use SaQle\Dao\Operations\Crud\{SelectOperation, InsertOperation, DeleteOperation, UpdateOperation, TotalOperation, TableCreateOperation, RunOperation, TableDropOperation};
 use SaQle\Dao\Model\Exceptions\NullObjectException;
 use function SaQle\Exceptions\{modelnotfoundexception};
 use SaQle\Dao\Model\Schema\Model;
 use SaQle\Image\Image;
 use SaQle\Commons\{DateUtils, UrlUtils, StringUtils};
 use SaQle\Services\Container\ContainerService;
 use SaQle\Services\Container\Cf;
 use SaQle\Dao\Field\Relations\{One2One, Many2Many};
 use SaQle\Dao\Model\Manager\Handlers\{PathsToUrls, EagerLoadAssign, TypeCast, FormatCmdt, FormattedChecker};
 use SaQle\Core\Chain\{Chain, DefaultHandler};
 use SaQle\Dao\Model\Manager\Trackers\EagerTracker;
 use SaQle\Core\Assert\Assert;
 use Closure;
 use SaQle\Dao\Model\Manager\Modes\FetchMode;
 use SaQle\Dao\Model\TempId;
 use SaQle\Dao\Model\Interfaces\{IThroughModel, ITempModel};
 use SaQle\Dao\Model\Collection\ModelCollection;

class ModelManager extends IModelManager{
	 use DateUtils, UrlUtils, StringUtils;

	 private function eager_load(){
	 	 return $this->get(tracker_active: true);
	 }

	 //return all the rows found
	 public function all(){
	 	 return $this->get();
	 }

	 //return the first row if its available otherwise throw an exception
	 public function first(){
	 	 $response = $this->get();
	 	 if(!$response){
	 	 	$table = $this->get_context_tracker()->find_table_name(0);
	 	 	throw new NullObjectException(table: $table);
	 	 }
	 	 return $response[0];
	 }

     //return the first row if its available otherwise return null
	 public function first_or_default(){
	 	 $response = $this->get();
	 	 return $response ? $response[0] : null;
	 }

     //reteurn the last row if its available otherwise throw an exception
	 public function last(){
	 	 $response = $this->get();
	 	 if(!$response){
	 	 	throw NullObjectException(table: $this->get_context_tracker()->find_table_name(0));
	 	 }
	 	 return $response[count($response) - 1];
	 }

	 //return the last row if its available otherwise return null
	 public function last_or_default(){
	 	 $response = $this->get();
	 	 return $response ? $response[count($response) - 1] : null;
	 }

     private function fetch_related_data($foreign_schema, $foreign_key, $pkey_values, $field_name, $with, $tuning){
     	 //get name for temporary table.
     	 $long_foreign_model_name = $foreign_schema;
     	 $long_foreign_model_name_parts = explode("\\", $long_foreign_model_name);
     	 $foreign_model_name = array_pop($long_foreign_model_name_parts);
     	 $temp_table_name = strtolower($foreign_model_name)."_temp_ids";
     	 //get the db context class
     	 [$db_class, $foreign_table] = $foreign_schema::get_table_n_dbcontext();
     	 //create the temporary table
     	 TempId::db2($temp_table_name, TempId::class, $db_class)->create_table();
     	 //store the ids in the temporary table.
	 	 if($pkey_values){
	 	 	 $tmp_man = TempId::db2($temp_table_name, TempId::class, $db_class);
	 	 	 $tmp_man->config(fnqm: 'N-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY');
	 	 	 $values_to_add = [];
	 	 	 foreach($pkey_values as $id){
	 	 	 	 $values_to_add[] = ['id_value' => $id];
	 	 	 }
	 	 	 $added_ids = $tmp_man->add_multiple($values_to_add)->save();
	 	 }

	 	 $query_table_name = 'ranked_rows';

	 	 #This will select the temporary ids in related data
	 	 $ids_in_query  = TempId::db2($temp_table_name, TempId::class, $db_class)->select(['id_value'])->sql_info('select');

         $order_clause = "";
         $limit_records = 10000;
         $raw_filters = [];
         $selected_fields = null;
	 	 if($tuning){
	 	 	 $tuning_manager = $long_foreign_model_name::db();
	 	 	 $tuning_manager->config(fnqm: 'N-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY');
	 	 	 $tuning_manager = $tuning($tuning_manager);
	 	 	 $order_clause = $tuning_manager->get_order_clause();
	 	 	 (int)$limit_records = $tuning_manager->get_limit_records();
	 	 	 if($limit_records === 0){
	 	 	 	 $limit_records = 10000;
	 	 	 }

	 	 	 $tuning_manager->l_where("row_num__lte", (int)$limit_records);
	 	 	 $raw_filters = $tuning_manager->get_filter_manager()->get_raw_filter();
	 	 	 $selected_fields = $tuning_manager->get_selected_fields();
	 	 }

	 	 $cte_manager = $long_foreign_model_name::db()
	 	 ->config(fnqm: 'N-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY')
         ->select(null, function($fields) use ($foreign_key, $order_clause){
			 return "*, ROW_NUMBER() OVER (PARTITION BY {$foreign_key}{$order_clause}) AS row_num";
 	     })
 	     ->l_where("{$foreign_key}__in", $ids_in_query['sql'])
 	     ->sql_info('select');

         $outer_manager = $long_foreign_model_name::db(table_aliase: $query_table_name)
         ->config(fnqm: 'N-QUALIFY', ftnm: 'A-ONLY')
         ->select($selected_fields, function($fields) use ($foreign_key, $field_name){
 	 	     $json_string = "";
			 foreach ($fields as $_i => $f){
		         $keyparts = explode(".", $f);
		         $key = count($keyparts) === 3 ? $keyparts[2] : ( count($keyparts) === 2 ? $keyparts[1] : $keyparts[0]);
		         $json_string .= "'{$key}', {$key}";
		         if($_i < count($fields) - 1){
		         	$json_string .= ", ";
		         }
			 }
			 $sql_string  = "{$foreign_key}, CONCAT('[', GROUP_CONCAT(JSON_OBJECT(".$json_string.") SEPARATOR ', '), ']') AS {$field_name}";
			 return $sql_string;
 	     })->set_raw_filters($raw_filters)
 	     ->group_by([$foreign_key]);

 	     $testfilters = $outer_manager->get_filter_manager()->get_where_clause($outer_manager->get_context_tracker(), $outer_manager->get_configurations());

	 	 $outer_manager_query = $outer_manager->sql_info("select");

	 	 $finalsql = "WITH {$query_table_name} AS ({$cte_manager['sql']}) {$outer_manager_query['sql']}";

	 	 $finalmanager = $long_foreign_model_name::db()->sqlndata($finalsql, $testfilters->data ? $testfilters->data : null);
	 	 if($with){
	 	 	 $withcallbacks = $this->get_select_manager()->get_withcallbacks();
	 	 	 $finalmanager->with($with, !empty($withcallbacks) ? $withcallbacks : null);
	 	 }
	 	 
	 	 $related_data = $finalmanager->eager_load();

     	 //drop the temporary table
     	 TempId::db2($temp_table_name, TempId::class, $db_class)->drop_table();

     	 return $related_data;
     }

     private function unpack_related_data($data, $is_eager_loading){
     	 $tmp_data           = $data;
     	 $tracker            = EagerTracker::activate();
         $existing_relations = $tracker::get_relations();
         $exr_count          = count($existing_relations);
         $exr_last_index     = $exr_count > 0 ? $exr_count - 1 : 0; 
         $former_rel_field   = isset($existing_relations[$exr_last_index]) ? $existing_relations[$exr_last_index]->field : '';
         $former_ref_key     = isset($existing_relations[$exr_last_index]) ? $existing_relations[$exr_last_index]->fk : '';

         if($is_eager_loading){
         	 $data = [];
         	 foreach($tmp_data as $td){
                 $json_data = json_decode($td->$former_rel_field);
         	 	 $data = !is_null($json_data) ? array_merge($data, $json_data) : $data;
         	 }
	 	 }

	 	 return [$former_rel_field, $former_ref_key, $data];
     }

     private function get_auto_include(Model $model){
	 	$auto_includes = [];
	 	foreach($model->meta->fields as $fn => $fv){
	 		if($fv instanceof Relation && $fv->eager){
	 			$auto_includes[] = ['relation' => $fv->get_relation(), 'with' => '', 'tuning' => null];
	 		}
	 	}
	 	return $auto_includes;
	 }

     private function process_includes($schema_instance, $ass_model_class, $data, $is_eager_loading, $data_formatted){
	 	 $explicit_includes  = $this->get_select_manager()->get_includes();
	 	 $auto_includes      = $this->get_auto_include($schema_instance);
	 	 $include_instances  = array_merge(array_column($explicit_includes, 'relation'), array_column($auto_includes, 'relation'));

	 	 if(!$include_instances){
	 	 	 return $data;
	 	 }

	 	 $nested_includes    = array_merge(array_column($explicit_includes, 'with'), array_column($auto_includes, 'with'));
	 	 $includes_tuning    = array_merge(array_column($explicit_includes, 'tuning'), array_column($auto_includes, 'tuning'));

         $tracker            = EagerTracker::get();
         [
         	 $former_rel_field, 
             $former_ref_key, 
             $data
         ]                   = $this->unpack_related_data($data, $is_eager_loading);
         if(!$data_formatted){
         	 $data           = $this->format_get_data($schema_instance, $ass_model_class, $data);
         }
	 	 
	 	 foreach($include_instances as $index => $ins){
	 	 	 $tracker::add_relation($ins);
	 	 	 $with          = $nested_includes[$index];
	 	 	 $tuning        = $includes_tuning[$index];
	 	 	 $include_data  = $this->process_include($ins, $with, $tuning, $data);
	 	 	 foreach($data as $d){
	 	 	 	 $rel_field     = $include_data['rel_field'];
	 	 	 	 $ref_key       = $include_data['ref_key'];
	 	 	 	 $ref_key_value = $d->$ref_key;
	 	 	 	 $d->$rel_field = $include_data['rel_data'][$ref_key_value] ?? ($include_data['is_multiple'] ? [] : null);
	 	 	 }
	 	 }

	 	 if($is_eager_loading){
         	 $consolidated_data  = [];
         	 foreach($data as $r){
         	 	 $former_ref_key_val = $r->$former_ref_key;
         	 	 if(!array_key_exists($former_ref_key_val, $consolidated_data)){
         	 	 	 $consolidated_data[$former_ref_key_val] = (Object)[$former_ref_key => $former_ref_key_val, $former_rel_field => []];
         	 	 }
         	 	 $consolidated_data[$former_ref_key_val]->$former_rel_field[] = $r;
         	 }
         	 $data = array_values($consolidated_data);
         	 foreach($data as $d){
         	 	$d->$former_rel_field = json_encode($d->$former_rel_field);
         	 }
	 	 }

	 	 return $data;
     }

     private function process_include($ins, $with, $tuning, $data){
     	 $fmodel       = $ins->fmodel;
 	 	 $pkey         = $ins->pk;
	 	 $fkey         = $ins->fk;
	 	 $pkey_values  = array_unique(array_column($data instanceof ModelCollection ? $data->items() : $data, $pkey));

	 	 $pkey_values  = array_filter($pkey_values, function($v){
	 	 	 return trim($v) !== "" && !is_null($v);
	 	 });

	 	 $raw_data     = $this->fetch_related_data($fmodel, $fkey, $pkey_values, $ins->field, $with, $tuning);
	 	 $rel_data     = [];
	 	 $field        = $ins->field;
	 	 $fmodelinstance = new $fmodel();
	 	 foreach($raw_data as $rd){
	 	 	 $pointer_value            = $rd->$fkey;
	 	 	 $the_rows                 = json_decode(preg_replace('/,(\s*])/', '$1', $rd->$field));
	 	 	 $the_rows                 = $this->format_get_data($fmodelinstance, $fmodel, $the_rows);
	 	 	 $rel_data[$pointer_value] = $ins->multiple ? $the_rows : ($the_rows[0] ?? null);
	 	 }
	 	 return [
	 	 	'ref_key'     => $pkey,
	 	 	'raw_data'    => $raw_data,
	 	 	'rel_data'    => $rel_data,
	 	 	'rel_field'   => $field,
	 	 	'is_multiple' => $ins->multiple
	 	 ];
     }

     private function has_get_data_been_formatted($data){
     	 if(count($data) === 0)
     	 	return true;

		 $allHaveProperty = true;
		 foreach($data as $d){
		     if(!property_exists($d, '_sql_data_formatted')){
		         $allHaveProperty = false;
		         break; 
		     }
		 }

		 return $allHaveProperty;
     }

     private function get_file_configurations(Model $model){
     	 $fc = [];
     	 foreach($model->meta->file_field_names as $ffn){
     	 	$fc[$ffn] = $model->meta->fields[$ffn]->get_field_attributes();
     	 }
     	 return $fc;
     }

     private function format_get_data($model_instance, $ass_model_class, $data){
     	 if(!$this->has_get_data_been_formatted($data)){
     	 	 $chain = new Chain();
		 	 $chain->add(new DefaultHandler());

		 	 //Mark the data as having been formatted
		     $chain->add(new FormattedChecker());

		 	 //Cast the row data to value object type
		 	 $chain->add(new TypeCast(type: $ass_model_class));

		     //Process the data through the chain.
		     if($chain->is_active()){
		     	 $processed = [];
		     	 foreach($data as $row){
		     	 	 $processed[] = $chain->apply($row);
		     	 }
		     	 $data = $processed;
		     }

		     //return a typed model collection
		     //return new ModelCollection(type: $ass_model_class, elements: $data);
		     return $data;

		 	 /*/Format date and timestamps to human readable forms
		     if($model_instance->meta->auto_cmdt && $model_instance->meta->format_cmdt && $model_instance->meta->cmdt_type === 'PHPTIMESTAMP'){
		     	 $createdat_name = $model_instance->meta->created_at_field;
		     	 $modifiedat_name = $model_instance->meta->modified_at_field;
		     	 $chain->add(new FormatCmdt(cat_name: $createdat_name, mat_name: $modifiedat_name));
		     }

		     //Format file paths to urls
		     $file_configurations = $this->get_file_configurations($model_instance);
		     if(count($file_configurations) > 0){
		     	 $chain->add(new PathsToUrls(model: $model_instance, config: $file_configurations));
		     }*/
     	 }
	     	 
         return $data;
     }

	 private function get(bool $tracker_active = false){
	 	 #acquire the table being processed
	 	 $table_name   = $this->get_context_tracker()->find_table_name(0);
	 	 #acquire the dao model.
	 	 $model_instance = $this->get_model($table_name);
	 	 $model_class    = $model_instance::class;
	 	 $ass_model_class = $model_class;

	 	 #activate tracker and add current model to it.
	 	 $tracker = EagerTracker::activate();
	 	 if(!$tracker_active){
	 	 	 $tracker::reset();
	 	 }
	 	 $tracker->add_model($model_class);

	 	 #if soft delete is available, apply the fetch mode
	 	 /*if($model_instance->meta->soft_delete){
	 	 	 $mode = $this->fetch_mode();
	 	 	 if($mode === FetchMode::NON_DELETED){
	 	 	 	 $this->where($table_name.'.deleted__eq', 0);
	 	 	 }elseif($mode === FetchMode::DELETED){
	 	 	 	 $this->where($table_name.'.deleted__eq', 1);
	 	 	 }
     	 }*/

	 	 #setup a select command
	 	 $sql_info = $this->sql_info(operation: 'select');
	 	 if($this->is_custom_sql()){
	 	 	 $sql_info = $this->get_sqlndata();
	 	 }
	 	 $this->crud_command = new SelectCommand(new SelectOperation($this->get_connection()), sql: $sql_info['sql'], data: $sql_info['data']);

	 	 #execute command and return response
	 	 $rows = $this->crud_command->execute();

         $data_formatted = false;
	 	 if(!$tracker_active && $model_class !== "SaQle\Dao\Model\TempId"){
	 	 	 $rows = $this->format_get_data($model_instance, $ass_model_class, $rows);
	 	 	 $data_formatted = true;
	 	 }

	 	 #process includes and return
	 	 return $this->process_includes($model_instance, $ass_model_class, $rows, $tracker_active, $data_formatted);
	 }

	 public function total(){
	 	 /*setup a select command*/
	 	 $this->crud_command = new TotalCommand(
	 	 	 new TotalOperation($this->get_connection()),
	 	 	 where_clause:  $this->get_filter_manager()->get_where_clause($this->get_context_tracker(), $this->get_configurations()),
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
     	 $include_field = $model->is_relation_field($field);

     	 if(!$include_field){
     	 	throw new \Exception("{$field} This is not an includable field!");
     	 }

     	 return [$include_field, $model];
	 }

	 public function with(array|string $field, $callable = null){
	 	 $fields    = is_array($field) ? $field : [$field];

	 	 /**
	 	  * @var $callable is either a null, a callable or an array of callables.
	 	  * */
	 	 $callables = [];
	 	 if(!is_array($callable) && !is_null($callable)){
	 	 	 Assert::isCallable($callable, 'Parameter callable must be a callable!');
	 	 	 $callables[$fields[0]] = $callable;
	 	 }elseif(is_array($callable)){ //ensure its an associative array and all values are callables.
	 	 	 Assert::isNonEmptyMap($callable);
	 	 	 Assert::allIsCallable(array_values($callable));

	 	 	 $callables = $callable;
	 	 }

	 	 $select_manager = $this->get_select_manager();
	 	 foreach($fields as $wf_key => $wf){ //for each with field key => value pair
	 	 	 if(is_int($wf_key)){ //if key is integer, it means value is the field name.
	 	 	 	 #split the field using dot as separator to see if nested.
		 	 	 $field_parts = explode(".", $wf);
		 	 	 #check if the first field is really an include field
		 	 	 $first_wf = array_shift($field_parts);
		 	 	 [$field, $model] = $this->check_with($first_wf);
		 	 	 $select_manager->add_include([
		 	 	 	'with'     => implode(".", $field_parts), 
		 	 	 	'relation' => $field, 
		 	 	 	'tuning'   => $callables[$first_wf] ?? null
		 	 	 ]);
		 	 	 unset($callables[$first_wf]);
	 	 	 }else{ //if key is string, it means key is the field name
	 	 	 	 [$field, $model] = $this->check_with($wf_key);
	 	 	 	 $select_manager->add_include([
		 	 	 	'with'     => $wf, 
		 	 	 	'relation' => $field, 
		 	 	 	'tuning'   => $callables[$wf_key] ?? null
		 	 	 ]);
		 	 	 unset($callables[$wf_key]);
	 	 	 }
	 	 }

	 	 $select_manager->set_withcallbacks($callables);
	 	 return $this;
	 }

     /**
      * Short cut to eager load author and modifier
      * user information
      * */
	 public function with_authors(){
	 	 [$a_field, $model] = $this->check_with("author");
	 	 [$m_field] = $this->check_with("modifier");

	 	 if($model->meta->auto_cm){
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
	 	 if($model->meta->enable_multitenancy){
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
     	 $files = array_values($this->file_data);
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
     	 if(array_key_exists("is_duplicate", $this->operation_status)){
     	 	 switch($this->operation_status['duplicate_action']){
	     	 	 case 'IGNORE_DUPLICATE':
	     	 	     /**
	     	 	      * Do nothing. This tells the modelmanager to add records despite the duplicates.
	     	 	      * */
	     	 	 break;
	     	 	 case 'BYPASS_DUPLICATE':
	     	 	     /**
	     	 	      * Remove duplicate data from the data container, duplicate entries record and file data if applicable..
	     	 	      * */
	     	 	     $duplicate_keys = array_keys($this->operation_status['duplicate_entries']);
		     	 	 foreach($duplicate_keys as $key){
		     	 	 	 Assert::keyExists($this->insert_data_container["data"], $key);
		     	 	 	 Assert::keyExists($this->operation_status['duplicate_entries'], $key);

		     	 	 	 unset($this->insert_data_container["data"][$key]);
		     	 	 	 unset($this->operation_status['duplicate_entries'][$key]);

		     	 	 	 if(isset($this->file_data[$key])){
		     	 	 	 	unset($this->file_data[$key]);
		     	 	 	 }

		     	 	 	 if(isset($this->insert_data_container["prmkeyvalues"][$key])){
		     	 	 	 	unset($this->insert_data_container["prmkeyvalues"][$key]);
		     	 	 	 }
		     	 	 }

		     	 	 Assert::isNonEmptyMap($this->insert_data_container["data"], "Save attempt on an empty data container after removing duplicates!");
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
	     	 	 	 $duplicate_keys = array_keys($this->operation_status['duplicate_entries']);
		     	 	 foreach($duplicate_keys as $key){
		     	 	 	 Assert::keyExists($this->insert_data_container["data"], $key);
		     	 	 	 Assert::keyExists($this->operation_status['duplicate_entries'], $key);

		     	 	 	 /**
		     	 	 	  * replace the existing value with the incoming value.
		     	 	 	  * */
		     	 	 	 $this->operation_status['duplicate_entries'][$key] = $this->insert_data_container["data"][$key];

		     	 	 	 unset($this->insert_data_container["data"][$key]);

		     	 	 	 if(isset($this->insert_data_container["prmkeyvalues"][$key])){
		     	 	 	 	unset($this->insert_data_container["prmkeyvalues"][$key]);
		     	 	 	 }
		     	 	 }
	     	 	 break;
	     	 	 case 'RETURN_EXISTING':
	     	 	 	 /**
	     	 	 	  * Remove duplicate data from the data container and the files container and leave the duplicate entries records as they will be 
	     	 	 	  * returned as is to the caller.
	     	 	 	  * */
	     	 	 	 $duplicate_keys = array_keys($this->operation_status['duplicate_entries']);
		     	 	 foreach($duplicate_keys as $key){
		     	 	 	 Assert::keyExists($this->insert_data_container["data"], $key);
		     	 	 	 Assert::keyExists($this->operation_status['duplicate_entries'], $key);

		     	 	 	 unset($this->insert_data_container["data"][$key]);

		     	 	 	 if(isset($this->file_data[$key])){
		     	 	 	 	unset($this->file_data[$key]);
		     	 	 	 }

		     	 	 	 if(isset($this->insert_data_container["prmkeyvalues"][$key])){
		     	 	 	 	unset($this->insert_data_container["prmkeyvalues"][$key]);
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

     private function save_changes(){
     	 try{
     	 	 #acquire the table being processed
 	         $table_name   = $this->get_context_tracker()->find_table_name(0);
 	         #acquire the dao model.
 	         $model_instance = $this->get_model($table_name);
 	         #implemented interfaces
 	         $interfaces = class_implements($model_instance::class);
 	         $ass_model_class = $model_instance::class;

     	 	 Assert::isNonEmptyMap($this->insert_data_container["data"], "$ass_model_class: Save attempt on an empty data container!");
     	     $this->assert_duplicates();

     	     #At this point, if there is still data to be saved in the data container, save it.
     	     $results = null;
     	     if($this->insert_data_container["data"]){
	 	         #setup insert command.
     	     	 $sql_info = $this->sql_info(operation: 'insert');
	 	         $this->crud_command = new InsertCommand(
			 	 	 new InsertOperation($this->get_connection()),
			 	 	 prmkeytype: $this->insert_data_container["prmkeytype"],
			 	 	 table:      $this->get_context_tracker()->find_table_name(0),
			 	 	 sql:        $sql_info['sql'],
			 	 	 data:       $sql_info['data']
			 	 );
			 	 #execute command and return response
			 	 $response = $this->crud_command->execute();
			 	 #save any files in the files container.
			 	 $this->auto_save_files();

                 $primary_key_values = [];
			 	 if($this->insert_data_container["prmkeytype"] === 'GUID'){
			 	 	 $primary_key_values = array_values($this->insert_data_container["prmkeyvalues"]);
			 	 }else{
					 for($i = 0; $i < $response->row_count; $i++){
					    $primary_key_values[] = $response->last_insert_id + $i;
					 }
			 	 }

			 	 #fetch all the data just saved.
			 	 if(in_array(ITempModel::class, $interfaces) || in_array(IThroughModel::class, $interfaces)){
			 	 	 $man = $ass_model_class::db2($table_name, $model_instance::class, $this->get_dbcontext_class());
			 	 }else{
			 	 	 $man = $ass_model_class::db();
			 	 }
		 	 	 $results = $man->where($this->insert_data_container["prmkeyname"]."__in", $primary_key_values)
		 	 	 ->tomodel($this->get_tomodel())->get();
			 }

			 #now deal with the duplicates.
			 if(!$results){
			 	 if($this->get_tomodel()){
			 	 	 $collection_class = $ass_model_class::get_collection_class();
			 	 	 $results = new $collection_class([]);
			 	 }else{
			 	 	 $results = [];
			 	 }
			 }

	 	 	 if(array_key_exists("is_duplicate", $this->operation_status) && $this->operation_status['duplicate_entries']) {
	 	 	 	 switch($this->operation_status['duplicate_action']){
	 	 	 	 	 case 'UPDATE_ON_DUPLICATE';
	 	 	 	 	     $unique_together = $this->operation_status['unique_together'];
					 	 
	 	 	 	 	     foreach($this->operation_status['duplicate_entries'] as $dk => $dv){
	 	 	 	 	     	 if(str_contains($ass_model_class, 'Throughs')){
					 	 	     $man = $ass_model_class::db2($table_name, $model_instance::class, $this->get_dbcontext_class());
						 	 }else{
						 	 	 $man = $ass_model_class::db();
						 	 }
	 	 	 	 	     	 $unique_fields = $this->operation_status['unique_fields'][$dk];
	 	 	 	 	     	 $man           = $this->build_update_manager($man, $unique_fields, $unique_together);
	 	 	 	 	     	 $updateresp    = $man->set($dv)->update(multiple: false, force: true);
	 	 	 	 	     	 if($updateresp){
	 	 	 	 	     	 	$results[]  = $updateresp;
	 	 	 	 	     	 }
	 	 	 	 	     }
	 	 	 	 	 break;
	 	 	 	 	 case "RETURN_EXISTING":
	 	 	 	 	     #Inject the duplicate data into the result.
	 	 	 	 	     if($this->get_tomodel()){
	 	 	 	 	     	 foreach($this->operation_status['duplicate_entries'] as $dk => $dv){
	 	 	 	 	     	 	$results->add(new $ass_model_class(...(array)$dv));
	 	 	 	 	         }
	 	 	 	 	     }else{
	 	 	 	 	     	 $results = array_merge($results, array_values($this->operation_status['duplicate_entries']));
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
     	 [$clean_data, $file_data, $is_duplicate, $action_on_duplicate] = $model->prepare_insert_data($data, $this->request, $table, $model::class, $this->get_dbcontext_class(), $skip_validation);

     	 $entry_key = spl_object_hash((object)$clean_data);

     	 if($is_duplicate !== false){
     	 	 if(array_key_exists("is_duplicate", $this->operation_status)){
     	 	 	 $this->operation_status['duplicate_entries'][$entry_key] = $is_duplicate[0];
     	 	 	 $this->operation_status['unique_fields'][$entry_key] = $is_duplicate[1];
     	 	 }else{
     	 	 	 $this->operation_status['is_duplicate'] = true;
     	 	 	 $this->operation_status['duplicate_action'] = $action_on_duplicate;
     	 	 	 $this->operation_status['unique_together'] = $is_duplicate[2];

     	 	 	 $this->operation_status['duplicate_entries'] = [];
     	 	 	 $this->operation_status['duplicate_entries'][$entry_key] = $is_duplicate[0];

     	 	 	 $this->operation_status['unique_fields'] = [];
     	 	     $this->operation_status['unique_fields'][$entry_key] = $is_duplicate[1];
     	 	 }
     	 }

     	 $this->insert_data_container["prmkeyname"] = $model->meta->pk_name;
     	 $this->insert_data_container["prmkeytype"] = $model->meta->pk_type;
     	 if($this->insert_data_container["prmkeytype"] === 'GUID'){
     	 	 $this->insert_data_container["prmkeyvalues"][$entry_key] = $clean_data[$model->meta->pk_name];
     	 }
     	 
     	 $this->file_data[$entry_key] = $file_data;
     	 $this->insert_data_container["data"][$entry_key] = $clean_data;
     	 return $this;
     }

     public function add_multiple(array $data, bool $skip_validation = false){
     	foreach($data as $dk => $dv){
     		$this->add($dv, $skip_validation);
     	}
     	return $this;
     }

     public function save(){
     	 $saved_data = $this->save_changes();
     	 if(!$saved_data)
     	 	return new \Exception("Could not save object");

     	 if(count($this->insert_data_container["data"]) > 1)
     	 	return $saved_data;

     	 return $saved_data[0];
     }

     // deletes
     private function soft_delete(){
     	 /*setup an update command*/
	 	 $this->crud_command = new UpdateCommand(
	 	 	 new UpdateOperation($this->get_connection()),
	 	 	 where_clause:  $this->get_filter_manager()->get_where_clause($this->get_context_tracker(), $this->get_configurations()),
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
	 	 	 where_clause:  $this->get_filter_manager()->get_where_clause($this->get_context_tracker(), $this->get_configurations()),
	 	 	 table_name:    $this->get_context_tracker()->find_table_name(0),
	 	 	 database_name: $this->get_context_tracker()->find_database_name(0)
	 	 );
	 	 /*execute command and return response*/
	 	 return $this->crud_command->execute();
     }

     public function delete(bool $permanently = false){
     	 return $permanently ? $this->hard_delete() : $this->soft_delete();
     }

     //updates

     /**
      * Set the data state of the object that is either being saved or updated
      * at the moment, this only happens when you initialize
      * the manager from a model.
      * */
     public function set_data_state(array $data_state){
     	 $this->data_state = $data_state;
     	 return $this;
     }

     /**
      * Set collects key => value data reperesenting field names and the new values to update, 
      * Sometimes you need to call set multiple times to have the data you would like to update
      * 
      * @param array $data.
      * */
     public function set(array $data){
     	 $this->update_data_container["data"] = array_merge($this->update_data_container["data"], $data);
     	 return $this;
     }

     public function set_multiple(array $data){
     	 foreach($data as $dv){
     		$this->set($dv);
     	 }
     	 return $this;
     }

     public function update(bool $multiple = false, bool $force = false){
     	 $table = $this->get_context_tracker()->find_table_name(0); //name of current table being manipulated
     	 $model = $this->get_model($table); //the model schema instance for the table.
     	 $ass_model_class = $model::class;
         #Make sure the update container has some data
     	 Assert::isNonEmptyMap($this->update_data_container['data'], "$ass_model_class: Update attempt on an empty data container!");
     	 #Clean up the update data, prepare files
     	 [$clean_data, $file_data, $is_duplicate, $action_on_duplicate] = $model->prepare_update_data($this->update_data_container['data'], $this->request, $this->data_state);
     	 #For updates, if there is a duplicate, just abort the update operation.
     	 if($is_duplicate !== false && !$force){
     	 	 throw new \Exception("Aborting update operation! The update operation will lead to duplicate entries in table: {$table}");
     	 }
     	 $this->file_data[] = $file_data;

     	 #setup an update command
     	 $where_clause = 
	 	 $this->crud_command = new UpdateCommand(
	 	 	 new UpdateOperation($this->get_connection()),
	 	 	 where_clause:  $this->get_filter_manager()->get_where_clause($this->get_context_tracker(), $this->get_configurations()),
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
	 	     $ud = $this->tomodel($this->get_tomodel())->eager_load();
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

     /**
      * Create a table in the database
      * */
     public function create_table(){
     	 $table = $this->get_context_tracker()->find_table_name(0); //name of current table being manipulated
     	 $model = $this->get_model($table); //the model schema instance for the table.
     	 $defs  = $model->get_field_definitions();

     	 /*setup a create command*/
	 	 $this->crud_command = new TableCreateCommand(
	 	 	 new TableCreateOperation($this->get_connection()),
	 	 	 table:  $table,
	 	 	 fields: implode(", ", $defs),
	 	 	 temporary: $model->meta->temporary
	 	 );

	 	 /*execute command and return response*/
	 	 return $this->crud_command->execute();
     }

     public function drop_table(){
     	 $table = $this->get_context_tracker()->find_table_name(0); //name of current table being manipulated
     	 $model = $this->get_model($table); //the model schema instance for the table.

		 /*setup a create command*/
	 	 $this->crud_command = new TableDropCommand(
	 	 	 new TableDropOperation($this->get_connection()),
	 	 	 table:  $table,
	 	 	 temporary: $model->meta->temporary
	 	 );

	 	 /*execute command and return response*/
	 	 return $this->crud_command->execute();
     }
}
?>