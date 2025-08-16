<?php
 namespace SaQle\Orm\Entities\Model\Manager;

 use SaQle\Orm\Operations\Crud\{SelectOperation, TotalOperation};
 use SaQle\Orm\Entities\Model\Exceptions\NullObjectException;
 use SaQle\Orm\Entities\Model\Schema\Model;
 use SaQle\Commons\{DateUtils, UrlUtils, StringUtils};
 use SaQle\Orm\Entities\Field\Relations\Many2Many;
 use SaQle\Orm\Entities\Model\Manager\Handlers\{TypeCast, FormattedChecker};
 use SaQle\Core\Chain\{Chain, DefaultHandler};
 use SaQle\Orm\Entities\Model\Manager\Trackers\EagerTracker;
 use SaQle\Core\Assert\Assert;
 use SaQle\Orm\Entities\Model\Manager\Modes\FetchMode;
 use SaQle\Orm\Entities\Model\TempId;
 use SaQle\Orm\Connection\Connection;
 use SaQle\Core\Observable\{Observable, ConcreteObservable};
 use SaQle\Core\FeedBack\FeedBack;
 use SaQle\Orm\Entities\Model\Observer\ModelObserver;
 use SaQle\Orm\Entities\Model\Interfaces\IOperationManager;
 use Exception;
 use Closure;

class ReadManager extends IReadManager implements Observable, IOperationManager {
	 use DateUtils, UrlUtils, StringUtils, ConcreteObservable {
		 ConcreteObservable::__construct as private __coConstruct;
	 }

	 public function __construct(){
	 	 parent::__construct();
	 	 $this->__coConstruct();
	 }

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
	 	 	$table = $this->ctxtracker->find_table_name(0);
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
	 	 	throw NullObjectException(table: $this->ctxtracker->find_table_name(0));
	 	 }
	 	 return $response[count($response) - 1];
	 }

	 //return the last row if its available otherwise return null
	 public function last_or_default(){
	 	 $response = $this->get();
	 	 return $response ? $response[count($response) - 1] : null;
	 }

     private function fetch_related_data($foreign_model, $foreign_key, $pkey_values, $field_name, $with, $tuning, $through){
     	 if($through){
     	 	 $original_foreignkey = $foreign_key;
     	     $foreign_key = $through[3];
     	     $other_key = $through[4];
     	 }

     	 /**
     	  * Store the ids of the objects to retrieve from foreign model table in the temporary table
     	  * 
     	  * These ID values could be in hundreds, therefore using an IN clause in the resulting SQL is not sound,
     	  * this is why they are kept in a temporary table to be referenced later
     	  * */
     	 TempId::create();
	 	 if($pkey_values){
	 	 	 $values_to_add = [];
	 	 	 foreach($pkey_values as $id){
	 	 	 	 $values_to_add[] = ['id_value' => $id];
	 	 	 }

	 	 	 TempId::new($values_to_add)->save();
	 	 }

	 	 /**
	 	  * Construct the sql statement that will select the id values from the temporary table above.
	 	  * 
	 	  * This is the statement that will be used in place of an IN clause in our final sql
	 	  * */
	 	 $temporary_ids_select_query = TempId::get()
	 	 ->config(fnqm: 'N-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY')
	 	 ->select(['id_value'])->get_select_sql_info();

         /**
          * Fine tune how the results from the foreign model table should be by injecting:
          * 
          * Order clause  : as deined in the with callback
          * Limit clause  : as defined in the with callback
          * Filter clause : as defined in the with callback
          * Select clause : as defined in the with callback
          * */
         $order_clause    = "";
         $limit_records   = 10000;
         $raw_filters     = [];
         $selected_fields = null;
	 	 if($tuning){ //turning is the with callback
	 	 	 $tuning_manager = $foreign_model::get()->config(fnqm: 'N-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY');
	 	 	 $tuning_manager = $tuning($tuning_manager);

	 	 	 $order_clause   = $tuning_manager->get_order_clause();
	 	 	 $limit_records  = (int)$tuning_manager->get_limit_records();
	 	 	 $limit_records  = $limit_records === 0 ? 10000 : $limit_records;
	 	 	 
	 	 	 $tuning_manager->l_where("row_num__lte", (int)$limit_records);

	 	 	 $raw_filters     = $tuning_manager->get_raw_filters();
	 	 	 $selected_fields = $tuning_manager->get_selected_fields();
	 	 }

         $through_columns = [];
         if($through){
         	 if(!$selected_fields){
         	 	 $through_model   = $through[1];
         	     $through_columns = array_values($through_model::state()->meta->actual_column_names);
         	 }

         	 $throughtablename = $through[0];
		 	 $cte_manager = $foreign_model::get()->config(fnqm: 'H-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY');
	         $cte_manager->select(null, function($fields) use ($foreign_key, $order_clause){
				 return implode(", ", $fields).", ROW_NUMBER() OVER (PARTITION BY {$foreign_key}{$order_clause}) AS row_num";
	 	     })
	 	     ->l_where("{$foreign_key}__in", $temporary_ids_select_query['sql'])
	 	     ->inner_join(table: $throughtablename, from: $original_foreignkey, to: $through[4]);
         }else{
         	 $cte_manager = $foreign_model::get()
	 	     ->config(fnqm: 'N-QUALIFY', ftnm: 'N-ONLY', ftqm: 'N-QUALIFY')
             ->select(null, function($fields) use ($foreign_key, $order_clause){
			     return "*, ROW_NUMBER() OVER (PARTITION BY {$foreign_key}{$order_clause}) AS row_num";
 	         })
 	         ->l_where("{$foreign_key}__in", $temporary_ids_select_query['sql']);
         }
 	     $cte_manager_query = $cte_manager->get_select_sql_info();

         $query_table_name = 'ranked_rows';
         $outer_manager = $foreign_model::get(tablealiase: $query_table_name)
         ->config(fnqm: 'N-QUALIFY', ftnm: 'A-ONLY')
         ->select($selected_fields, function($fields) use ($foreign_key, $field_name, $through_columns){
         	 $fields = array_merge($fields, $through_columns);
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
 	     })
 	     ->set_raw_filters($raw_filters)
 	     ->group_by([$foreign_key]);

 	     $testfilters = $outer_manager->get_wbuilder()->get_where_clause($outer_manager->get_context_tracker(), $outer_manager->get_configurations());

	 	 $outer_manager_query = $outer_manager->get_select_sql_info();

	 	 $finalsql = "WITH {$query_table_name} AS ({$cte_manager_query['sql']}) {$outer_manager_query['sql']}";

	 	 $finalmanager = $foreign_model::get()->sqlndata($finalsql, $testfilters->data ? $testfilters->data : null);
	 	 if($with){
	 	 	 $withcallbacks = $this->sbuilder->withcallbacks;
	 	 	 $finalmanager->with($with, !empty($withcallbacks) ? $withcallbacks : null);
	 	 }
	 	 
	 	 $related_data = $finalmanager->eager_load();

     	 //drop the temporary table
     	 TempId::drop();

     	 return $related_data;
     }

     private function unpack_related_data($data, $is_eager_loading){
     	 $tmp_data               = $data;
     	 $tracker                = EagerTracker::activate();
         $existing_relations     = $tracker::get_relations();
         $exr_count              = count($existing_relations);
         $exr_last_index         = $exr_count > 0 ? $exr_count - 1 : 0; 
         $former_rel_field       = '';
         $former_ref_key         = '';
         $ismanytomany           = false;
         if( isset($existing_relations[$exr_last_index]) ){
         	 $relation           = $existing_relations[$exr_last_index];
         	 $former_rel_field   = $relation->field;
         	 $former_ref_key     = $relation->fk;
         	 if($relation instanceof Many2Many){
         	 	 $through        = $relation->get_through_model();
         	 	 $former_ref_key = $through[3];
         	 	 $ismanytomany   = true;
         	 }
         }

         if($is_eager_loading){
         	 $data = [];
         	 foreach($tmp_data as $td){
                 $json_data = json_decode($td->$former_rel_field);
                 if(!is_null($json_data)){
                 	 $json_data = array_map(function($d) use ($td, $former_ref_key){
                 	 	 $d->$former_ref_key = $td->$former_ref_key;
                         return $d;
                     }, $json_data);
                 	 $data = array_merge($data, $json_data);
                 }
         	 }
	 	 }

	 	 return [$former_rel_field, $former_ref_key, $data];
     }

     private function get_auto_includes(Model $model){
	 	$auto_includes = [];
	 	foreach($model->meta->fields as $fn => $fv){
	 		if($fv instanceof Relation && $fv->eager){
	 			$auto_includes[] = ['relation' => $fv->get_relation(), 'with' => '', 'tuning' => null];
	 		}
	 	}
	 	return $auto_includes;
	 }

     private function process_includes($model_instance, $data, $is_eager_loading, $data_formatted){
	 	 $explicit_includes  = $this->sbuilder->includes;
	 	 $auto_includes      = $this->get_auto_includes($model_instance);
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
         	 $data           = $this->format_get_data($model_instance::class, $data);
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
	 	 }

	 	 return $data;
     }

     private function extrct_primarykey_values($pkey, $data){
     	 if(!$data)
     	 	return [];

     	 $keyvalues = [];
     	 if($data[0] instanceof Model){
     	 	 foreach($data as $d){
     	 	 	 $keyval = $d->$pkey;
     	 	 	 if(!is_null($keyval) && trim($keyval) !== ""){
     	 	 	 	 $keyvalues[] = $keyval;
     	 	 	 }
             }
     	 }else{
     	     $keyvalues = array_filter(array_column($data, $pkey), function($v){
	 	 	     return !is_null($v) && trim($v) !== "";
	 	     });
     	 }

     	 return array_unique($keyvalues);
     }

     private function process_include($ins, $with, $tuning, $data){
     	 $fmodel       = $ins->fmodel;
 	 	 $pkey         = $ins->pk;
 	 	 $pointerkey   = $ins->fk;
	 	 $fkey         = $ins->fk;
	 	 $through      = null;
	 	 $pkey_values  = $this->extrct_primarykey_values($pkey, $data);

	 	 if($ins instanceof Many2Many){
	 	 	 $through    = $ins->get_through_model();
	 	 	 $pointerkey = $through[3];
	 	 }
	 
	 	 $raw_data     = $this->fetch_related_data($fmodel, $fkey, $pkey_values, $ins->field, $with, $tuning, $through);
	 	 $rel_data     = []; 
	 	 $field        = $ins->field;
	 	 foreach($raw_data as $rd){
	 	 	 $pointer_value            = $rd->$pointerkey;
	 	 	 $the_rows                 = !is_array($rd->$field) ? json_decode(preg_replace('/,(\s*])/', '$1', $rd->$field)) : $rd->$field;
	 	 	 if(!is_array($rd->$field))
	 	 	 	 $the_rows             = $this->format_get_data($fmodel,  $the_rows);
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

     private function format_get_data($model_class, $data){
     	 if(!$this->has_get_data_been_formatted($data)){
     	 	 $chain = new Chain();
		 	 $chain->add(new DefaultHandler());

		 	 //Mark the data as having been formatted
		     $chain->add(new FormattedChecker());

		 	 //Cast the row data to value object type
		 	 $chain->add(new TypeCast(type: $model_class));

		     //Process the data through the chain.
		     if($chain->is_active()){
		     	 $processed = [];
		     	 foreach($data as $row){
		     	 	 $processed[] = $chain->apply($row);
		     	 }
		     	 $data = $processed;
		     }

		     return $data;
     	 }
	     	 
         return $data;
     }

	 private function get(bool $tracker_active = false){
	 	 $table_name     = $this->ctxtracker->find_table_name(0); //get db table name
	 	 $model_instance = $this->get_model($table_name); //get model instance associated with table

	 	 //activate tracker and add current model to it.
	 	 $tracker = EagerTracker::activate();
	 	 if(!$tracker_active){
	 	 	 $tracker::reset();
	 	 }
	 	 $tracker->add_model($model_instance::class);

	 	 #if soft delete is available, apply the fetch mode
	 	 /*if($model_instance->meta->soft_delete){
	 	 	 $mode = $this->fetch_mode();
	 	 	 if($mode === FetchMode::NON_DELETED){
	 	 	 	 $this->where($table_name.'.deleted__eq', 0);
	 	 	 }elseif($mode === FetchMode::DELETED){
	 	 	 	 $this->where($table_name.'.deleted__eq', 1);
	 	 	 }
     	 }*/

	 	 $sql_info = $this->get_select_sql_info();
	 	 if($this->is_custom_sql()){
	 	 	 $sql_info = $this->get_sqlndata();
	 	 }
	 	 $pdo = resolve(Connection::class, DB_CONTEXT_CLASSES[$this->dbclass]);
	 	 $operation = new SelectOperation(sql: $sql_info['sql'], data: $sql_info['data']);

	 	 //send pre select signal to observers
	 	 $preobservers = array_merge(
	 	 	 ModelObserver::get_model_observers('before', 'select', $model_instance::class), 
	 	 	 ModelObserver::get_shared_observers('before', 'select')
	 	 );
 	     $this->quick_notify(
 	     	 observers: $preobservers,
 	     	 code: FeedBack::OK, 
 	     	 data: [
 	     	 	 'table'         => $table_name, 
 	     	 	 'sql'           => $sql_info['sql'], 
 	     	 	 'prepared_data' => $sql_info['data'],
 	     	 	 'dbclass'       => $this->dbclass,
 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
 	     	 	 'timestamp'     => time(),
 	     	 	 'model'         => $model_instance::class
 	     	 ]
 	     );
 	     //get rows
	 	 $rows = $operation->select($pdo);

         $data_formatted = false;
	 	 if(!$tracker_active && $model_instance::class !== "SaQle\Orm\Entities\Model\TempId"){
	 	 	 $rows = $this->format_get_data($model_instance::class, $rows);
	 	 	 $data_formatted = true;
	 	 }

	 	 //process includes and return
	 	 $result = $this->process_includes($model_instance, $rows, $tracker_active, $data_formatted);

	 	 //send post select signal to observers
	 	 $postobservers = array_merge(
	 	 	 ModelObserver::get_model_observers('after', 'select', $model_instance::class), 
	 	 	 ModelObserver::get_shared_observers('after', 'select')
	 	 );
 	     $this->quick_notify(
 	     	 observers: $postobservers,
 	     	 code: FeedBack::OK, 
 	     	 data: [
 	     	 	 'table'         => $table_name, 
 	     	 	 'sql'           => $sql_info['sql'], 
 	     	 	 'prepared_data' => $sql_info['data'],
 	     	 	 'dbclass'       => $this->dbclass,
 	     	 	 'db'            => DB_CONTEXT_CLASSES[$this->dbclass]['name'],
 	     	 	 'timestamp'     => time(),
 	     	 	 'model'         => $model_instance::class,
 	     	 	 'rows'          => $rows,
 	     	 	 'result'        => $result,
 	     	 ]
 	     );

 	     return $result;
	 }

	 public function total(){
	 	 try{
	 	 	 $pdo = resolve(Connection::class, DB_CONTEXT_CLASSES[$this->dbclass]);
		 	 $operation = new TotalOperation(
		 	 	 where_clause:  $this->wbuilder->get_where_clause($this->ctxtracker, $this->get_configurations()),
		 	 	 join_clause:   $this->jbuilder->construct_join_clause($this->ctxtracker),
		 	 	 limit_clause:  $this->lbuilder->construct_limit_clause(),
		 	 	 order_clause:  $this->obuilder->construct_order_clause(),
		 	 	 table_name:    $this->ctxtracker->find_table_name(0),
		 	 	 table_aliase:  $this->ctxtracker->find_table_aliase(0),
		 	 	 database_name: $this->ctxtracker->find_database_name(0)
		 	 );
		 	 return $operation->total($pdo);
	 	 }catch(Exception $ex){
     	 	 throw $ex;
	 	 }
	 }

	 //includes
	 private function check_with(string $field){
	 	 #Make sure this field is a navigation or foreign key field
     	 $table = $this->ctxtracker->find_table_name(0);
     	 #get the model associated with this table
     	 $model = $this->get_model($table);
     	 #get include field
     	 $include_field = $model->is_relation_field($field);

     	 if(!$include_field){
     	 	throw new Exception("{$field} This is not an includable field!");
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

	 	 foreach($fields as $wf_key => $wf){ //for each with field key => value pair
	 	 	 if(is_int($wf_key)){ //if key is integer, it means value is the field name.
	 	 	 	 #split the field using dot as separator to see if nested.
		 	 	 $field_parts = explode(".", $wf);
		 	 	 #check if the first field is really an include field
		 	 	 $first_wf = array_shift($field_parts);
		 	 	 [$field, $model] = $this->check_with($first_wf);
		 	 	 $this->sbuilder->add_include([
		 	 	 	'with'     => implode(".", $field_parts), 
		 	 	 	'relation' => $field, 
		 	 	 	'tuning'   => $callables[$first_wf] ?? null
		 	 	 ]);
		 	 	 unset($callables[$first_wf]);
	 	 	 }else{ //if key is string, it means key is the field name
	 	 	 	 [$field, $model] = $this->check_with($wf_key);
	 	 	 	 $this->sbuilder->add_include([
		 	 	 	'with'     => $wf, 
		 	 	 	'relation' => $field, 
		 	 	 	'tuning'   => $callables[$wf_key] ?? null
		 	 	 ]);
		 	 	 unset($callables[$wf_key]);
	 	 	 }
	 	 }

	 	 $this->sbuilder->withcallbacks = $callables;
	 	 return $this;
	 }
}
