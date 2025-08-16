<?php
namespace SaQle\Orm\Entities\Model\Schema;

use SaQle\Orm\Entities\Field\Relations\Interfaces\IRelation;
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\{Pk, TextField, OneToOne, OneToMany, FloatField, IntegerField, BigIntegerField, PhpTimestampField, ManyToMany, FileField, TinyTextField, DateField, TimeField, DateTimeField, TimestampField, BooleanField, VirtualField};
use SaQle\Orm\Entities\Field\Exceptions\FieldValidationException;
use SaQle\Security\Models\ModelValidator;
use SaQle\Commons\StringUtils;
use SaQle\Orm\Entities\Field\Types\Base\{Relation, RealField};
use SaQle\Orm\Entities\Model\Manager\{CreateManager, UpdateManager, DeleteManager, TruncateManager, ReadManager, RunManager};
use SaQle\Orm\Entities\Model\Interfaces\{IModel, ITableSchema};
use SaQle\Orm\Entities\Model\Collection\ModelCollection;
use SaQle\Core\Exceptions\Model\{UndefinedFieldException, MissingRequiredFieldsException};
use SaQle\Orm\Database\Db;
use SaQle\Orm\Database\Attributes\TransactionOutput;
use Exception;
use JsonSerializable;

use Booibo\Apps\Account\Models\IndustriesLabel;

abstract class Model implements ITableSchema, IModel, JsonSerializable{
	 use StringUtils;

     /**
      * Mark a model as read only. When a model instant is read only,
      * the fields cannot be read or set. This is a model that is only meant
      * accessing the meta information about a model
      * */
	 private bool $readonly;

	 /**
	  * Mark a model as having been instantiated via the static method.
	  * Models instantiated via the static method do not need data passed to the constructor.
	  * */
	 private static bool $from_static_method = false;

	 /**
	  * Mark a model as having received data from db
	  * When data is received from db, no validation is run on the data.
	  * */
	 private static bool $from_database = false;

     /**
      * A key => value array of raw model data: keys are field names.
      * Values may be simple, other model objects or arrays of model objects
      * */
	 protected private(set) array $data = []{
	 	 set(array $value){
		 	 $this->data = $value;
	 	 }

	 	 get => $this->data;
	 }

     /**
      * A memory record of data between the time it is set and the time
      * updates are made on the data
      * */
	 private $data_state = null;

	 /**
	  * Static cache to store shared metadata per class
	  * */
     private static array $shared_meta = [];

	 /**
	  * Virtual property that gets all the information about a table scehema
	  * */
	 public mixed $meta {
 	 	 get => $this->get_shared_meta(static::class);
	 }

	 protected static array $instances = [];
	 
	 public function __construct(...$kwargs){
	 	 if(!self::$from_static_method && !self::$from_database){
	 	 	 if(empty($kwargs))
	 	 	 	 throw new \Exception("Please provide data for: ".$this::class." when creating a new model instance!");

	 	 	 $this->on_model_input($kwargs);

	 	 	 $this->on_assign_relations($kwargs);

	 	 	 //convert any values coming in with column names to field names
	 	 	 $kwargs = $this->format_data($kwargs);

	         //ensure all the data keys are column names or fields defined on model
	 	 	 $this->assert_correct_fields($kwargs);

	 	 	 //fill in defaults for all the fields that haven't been provided
	         $kwargs = $this->fill_defaults($kwargs);

	         //make sure data is provided for all the required fields
	         $kwargs = $this->assert_required_fields($kwargs);

	         //run validation on the data
	         $this->run_data_validation($kwargs);

	 	 	 $this->on_model_validate($kwargs);
         }

	 	 $this->data = $kwargs;
	 	 $this->readonly = self::$from_static_method;
         self::$from_static_method = false;
     }

     //method to ensure shared meta per class
     private function get_shared_meta(string $class_name): TableInfo {
         //Check if meta already exists for this class
         if(!isset(self::$shared_meta[$class_name])) {
             $table_info = new TableInfo();
             [$db_class, $table_name] = self::get_table_n_dbcontext();
	 	     $table_info->db_table    = $table_name;
	 	     $table_info->db_class    = $db_class;
	 	     $table_info->model_class = $this::class;
             $this->model_setup($table_info);
             self::$shared_meta[$class_name] = $table_info;
         }

         //return the shared meta for this class
         return self::$shared_meta[$class_name];
     }

     //abstract method that must be implemented by concrete classes to define fields
     abstract protected function model_setup(TableInfo $meta): void;

     
     final public static function state(){
     	 self::$from_static_method = true;

         $called_class = get_called_class();
         if(!isset(self::$instances[$called_class])){
             self::$instances[$called_class] = new $called_class([]);
         }else{
         	 self::$from_static_method = false;
         }

         return self::$instances[$called_class];
     }

     final public static function from_db(...$data){
     	 self::$from_database = true;

         $called_class = get_called_class();
         $model = new $called_class(...$data);
         self::$from_database = false;
         return $model;
     }

	 public static function get_table_n_dbcontext(?string $dbclass = null){
	 	 $table = null;
	 	 $current_model_name = get_called_class();
	 	 
	 	 /**
	 	  * Note:
	 	  * All models must be registered with one or more database context classes,
	 	  * whether they are temporary, through or regular models
	 	  * 
	 	  * The first database context will be picked as the default database context unless
	 	  * the context class is passed to this method
	 	  * */
	 	 $dbclass = $dbclass ?? array_keys(DB_CONTEXT_CLASSES)[0];
 	 	 $models = new $dbclass()->get_models();
 	 	 $model_classes = array_values($models);
 	 	 if(in_array($current_model_name, $model_classes)){
 	 		 $table = array_keys($models)[array_search($current_model_name, $model_classes)];
 	 	 }

	 	 if(!$dbclass || !$table){
	 	 	 throw new Exception($current_model_name.": Model not registered with any db contexts!");
	 	 }

	 	 return [$dbclass, $table];
	 }

     public function get_validation_configurations(?array $field_names = null){
     	 $vc = [];
     	 foreach($this->meta->fields as $fn => $fv){
     	 	 $column_name = $fv->column_name;
     	 	 if(!$field_names || (($field_names && in_array($fn, $field_names)) || ($field_names && in_array($column_name, $field_names)))){
     	 	 	 $fvc = $fv->get_validation_configurations();
	     	 	 if($fvc){
	     	 	 	 $vc[$column_name] = $fvc;
	     	 	 }else{
	     	 	 	 $vc[$column_name] = false;
	     	 	 }
     	 	 }
     	 }
     	 return $vc;
     }

	 public function get_clean_data(array $data, string $operation = ''){
	 	 /**
	 	  * strip the data array of keys of the following:
	 	  * 1. All keys that are not field names of current model
	 	  * 2. PrimaryKey key: GUID key values will be auto generated by the application,
	 	  *    while AUTO key values are generated by the db engine on insert
	 	  * */
	 	 $original_data = $data;
     	 $tmp_data = [];
     	 $model_field_names = array_unique(array_merge(array_keys($this->meta->column_names), array_values($this->meta->column_names)));
     	 foreach(array_keys($data) as $_dk){
     	 	if(in_array($_dk, $model_field_names) && $_dk != $this->meta->pk_name && !in_array($_dk, $this->meta->nav_field_names)){
     	 		$tmp_data[$_dk] = $data[$_dk];
     	 	}
     	 }
     	 
     	 $data = $tmp_data;


         /**
          * The following field types will be excempted from validation, atleast for now.
          * 1. All navigation keys
          * 2. All file keys whose value in data is just file name. In the future, it should be possible
          *    to validate just the file name.
          * */
	 	 $vc = $operation == 'update' ? $this->get_validation_configurations(array_keys($data)) : $this->get_validation_configurations();
	 	 $file_names_only = [];
	 	 foreach($this->meta->file_field_names as $ffn){
	 	 	if(isset($data[$ffn]) && !is_array($data[$ffn])){
	 	 		$file_names_only[$ffn] = $data[$ffn];
	 	 	}
	 	 }

	 	 $exclude = array_merge(array_keys($file_names_only), $this->meta->nav_field_names);
	 	 foreach($vc as $vc_key => $vc_val){
	 	 	if(in_array($vc_key, $exclude)){
	 	 		unset($vc[$vc_key]);
	 	 	}
	 	 }

	 	 $validation_feedback = ModelValidator::validate($vc, $data);
		 if($validation_feedback["status"] !== 0){
			 throw new FieldValidationException([
			 	'model' => $this::class,
			 	'operation' => $operation,
			 	'dirty' => $validation_feedback['dirty']
			 ]);
		 }

		 /**
		  * In update operations, the primary key and value needs be reinserted into data here.
		  * TO DO: Find a way of avoiding this altogether.
		  * */
		 if($operation == 'update' && isset($original_data[$this->meta->pk_name])){
		 	$validation_feedback['clean'][$this->meta->pk_name] = $original_data[$this->meta->pk_name];
		 }
		 return array_merge($validation_feedback['clean'], $file_names_only);
	 }

     private function rename_uploaded_files($field, &$clean_data, $file_key, $data_state = null){
	 	$original_clean_data = $clean_data;
	 	if(is_array($clean_data[$file_key]['name'])){
             foreach($clean_data[$file_key]['name'] as $n_index => $n){
                 $clean_data[$file_key]['name'][$n_index] = $field->rename(
                 	$data_state ? $data_state : $original_clean_data, 
                 	$clean_data[$file_key]['name'][$n_index], 
                 	$n_index
                 );
             }
         }else{
             $clean_data[$file_key]['name'] = $field->rename(
             	$data_state ? $data_state : $original_clean_data, 
             	$clean_data[$file_key]['name']
             );
         }
	 }

	 private function prepare_file_data(&$clean_data, $data_state = null){
	 	 /**
	 	  * Prepare files data: Only the file names will be saved in db.
	 	  * 1. Rename the files using the rename callback that was provided on the model if any
	 	  * 2. Get the file path using the path callback that was provided on the model if any.
	 	  * 3. Reset the file values in clean data to file names only
	 	  * */
	 	 $file_data = [];
	 	 foreach($this->meta->file_field_names as $ffn){
	 	 	$db_col = $this->meta->column_names[$ffn];
	 	 	#if the file exists.
	 	 	if(isset($clean_data[$db_col]) && is_array($clean_data[$db_col])){
	 	 		 $file_config = [
	 	 		 	 'crop_dimensions'   => $this->meta->fields[$ffn]->crop_dimensions, 
	 	 		     'resize_dimensions' => $this->meta->fields[$ffn]->resize_dimensions
	 	 		 ];

	 	 		 //rename files
	 	 		 $this->rename_uploaded_files($this->meta->fields[$ffn], $clean_data, $db_col, $data_state);

	 	 		 //get the file path
	 	 		 $file_config['path'] = $this->meta->fields[$ffn]->path($data_state ? $data_state : $clean_data);
	 			 $file_data[$db_col] = (Object)['file' => $clean_data[$db_col], 'config' => $file_config];

	 			 //reset the file value in clean data to file names only.
	     	     $clean_data[$db_col] = is_array($clean_data[$db_col]['name']) ? implode("~", $clean_data[$db_col]['name']) : $clean_data[$db_col]['name'];
	 	 	}
	 	 }
	 	 return $file_data;
	 }

	 private function check_if_duplicate($data, $table_name = null, $model_class = null, $db_class = null){
	 	 if(!$this->meta->unique_fields){
	 	 	 return false;
	 	 }

	 	 /**
	 	  * Check that the fields provided are defined in model.
	 	  * */
	 	 $all_defined = true;
	 	 $unique_fields = [];

	 	 for($f = 0; $f < count($this->meta->unique_fields); $f++){
	 	 	 if( !isset($this->meta->fields[$this->meta->unique_fields[$f]]) ){
	 	 	 	 $all_defined = false;
	 	 	 	 break;
	 	 	 }

	 	 	 $db_col = $this->meta->column_names[$this->meta->unique_fields[$f]];
	 	 	 if(isset($data[$db_col])){
	 	 	 	 $unique_fields[$db_col] = $data[$db_col];
	 	 	 }
	 	 }

	 	 if(!$all_defined){
	 	 	throw new \Exception("One or more field names provided in meta unique_fields key is not valid!");
	 	 }

	 	 if( count($unique_fields) < count($this->meta->unique_fields) ){
	 	 	 return false;
	 	 }

         $unique_field_keys = array_keys($unique_fields);
         $first_field = array_shift($unique_field_keys);
         $dao = $this::class;
         if(str_contains($dao, "Throughs")){
         	 $manager = $dao::db2($table_name, $model_class, $db_class)->where($first_field."__eq", $unique_fields[$first_field]);
         }else{
         	 $manager = $dao::db()->where($first_field."__eq", $unique_fields[$first_field]);
         }
	 	 foreach($unique_field_keys as $uf){
	 	 	 $manager = $this->meta->unique_together ? $manager->where($uf."__eq", $unique_fields[$uf]) : $manager->or_where($uf."__eq", $unique_fields[$uf]);
	 	 }
	 	 $exists = $manager->limit(records: 1, page: 1)->first_or_default();

	 	 if(!$exists){
	 	 	 return false;
	 	 }

	 	 return [$exists, $unique_fields, $this->meta->unique_together];
	 }

     private function swap_properties_with_columns($data){
     	 $swapped = [];
     	 foreach($this->meta->fields as $pk => $pv){
     	 	 $ck = $this->meta->fields[$pk]->column_name;
     	 	 if( array_key_exists($ck, $data) || array_key_exists($pk, $data) ){
     	 	 	 $swapped[$ck] = $data[$ck] ?? $data[$pk];
     	 	 }
     	 }
     	 return $swapped;
     }

	 public function get_field_definitions(){
	 	 $defs = [];
     	 foreach($this->meta->fields as $fn => $fv){
     	 	 $fd = $fv->get_field_definition();
     	 	 if($fd){
     	 	 	 $defs[] = $fd;
     	 	 }
     	 }
     	 
     	 return $defs;
	 }

     private function assert_field_defined(string $field_name, bool $throw_error = false) : array {
     	 //ensure field is defined in model meta
     	 if(array_key_exists($field_name, $this->meta->fields) || array_key_exists($field_name, $this->meta->column_names))
     	 	 return [$field_name, true];

     	 if($throw_error)
     	 	 throw new \Exception("The field ".$field_name." is not defined on the model ".$this::class);


     	 if(array_key_exists($field_name, $this->data)){
     	 	 return [$field_name, false];
     	 }

     	 throw new \Exception("There is no field or column named: ".$field_name." on the model ".$this::class);
     }

     public function get_field(string $field_name) : IField {
     	 [$field_name, $is_field] = $this->assert_field_defined($field_name, true);
     	 return $this->assign_field_context_and_value($field_name);
     }

     private function assign_field_context_and_value(string $field_name){
     	 $context           = $this->format_data($this->data, 'columns');
     	 $field             = $this->meta->fields[$field_name];
     	 $field->context    = $context;
     	 $field->value      = $context[$field->column_name] ?? null;
     	 $field->model_info = [
     	     'model' => $this->meta->model_class,
     	     'pk_name' => $this->meta->pk_name,
     	     'pk_value' => $this->data[$this->meta->pk_name] ?? '',
     	     'field_name' => $field_name
     	 ];
     	 return $field;
     }

	 public function __get($name){
	 	 [$field_name, $is_field] = $this->assert_field_defined($name);
	 	 if(!$is_field)
	 	 	 return $this->data[$field_name];

	 	 $field = $this->assign_field_context_and_value($field_name);
     	 return $field->render();
     }

     public function __set($name, $value){
     	 if($this->readonly){
             throw new \Exception("This is a read only model. You cannot modify the values!");
         }

         $this->assert_field_defined($name, true);

         //save the data state so we can track changes from now on
         if(!$this->data_state){
         	 $this->data_state = $this->get_data_state();
         }

         $this->data = array_merge($this->data, [$name => $value]);
     }

     public function __call(string $method, array $args){
     	 return $this->get_field($method);
     }

     public static function __callStatic(string $method, array $args){
     	 $modelclass = get_called_class();
     	 $model      = $modelclass::state();
     	 return $model->get_field($method);
     }

     /**
      * Check whether a field is a relation field defined on a model
      * and return the relation or false otherwise
      * */
     public function is_relation_field(string $field){
	 	 $includes = array_merge($this->meta->fk_field_names, $this->meta->nav_field_names);

	 	 if(!in_array($field, $includes)){
	 		$field = array_flip($this->meta->column_names)[$field];
	 		if(!in_array($field, $includes)){
	 			 return false;
	 		}
	 	 }

	 	 $instance = $this->meta->fields[$field] ?? null;
	 	 if(!$instance)
	 		 return false;

	 	 return $instance->get_relation();
	 }

     /**
      * Classify defined model fields into different groups
      * */
     private function classify_fields(){
	 	 $defined_field_names = $this->meta->defined_field_names;
	 	 $nk_field_names      = $this->meta->nav_field_names;
	 	 $fk_field_names      = $this->meta->fk_field_names;
	 	 $simple_fields       = array_diff($defined_field_names, array_merge($nk_field_names, $fk_field_names));
	 	 $nk_fields           = array_diff($defined_field_names, array_merge($simple_fields, $fk_field_names));
	 	 $fk_fields           = array_diff($defined_field_names, array_merge($simple_fields, $nk_field_names));
	 	 return [$defined_field_names, $simple_fields, $fk_fields, $nk_fields];
     }

     /**
      * Initialize the data state athe time of model instantiation
      * */
     private function get_data_state(){
	 	 $data_state = [];
	 	 [$defined_field_names, $simple_fields, $fk_fields, $nk_fields] = $this->classify_fields();

	     foreach($defined_field_names as $field){
	     	 if(in_array($field, $simple_fields)){
	     	 	 $data_state[$field] = $this->data[$field] ?? null;
	     	 }elseif(in_array($field, $fk_fields) || in_array($field, $nk_fields)){
	     	 	 $val = $this->data[$field] ?? null;
	     	 	 if($val instanceof IModel){
	     	 	 	 $relation = $this->is_relation_field($property_name);
	     	 	     $fk_name = $relation->fk;
	     	 	     $pk_values = $val->$fk_name;
	     	 	     $data_state[$field] = $pk_values;
	     	 	     if(in_array($field, $nk_fields) && $relation instanceof Many2Many){
	     	 	     	 [$table_name, $model, $ctx] = $relation->get_through_model();
	     	 	     	 $data_state[$field] = ['key' => $fk_name, 'values' => $pk_values, 'table' => $table_name, 'model' => $model, 'context' => $ctx];
	     	 	     }
	     	 	 }else{
	     	 	 	 $data_state[$field] = $val;
	     	 	 }
	     	 }
	     }
	     return $data_state;
	 }

     /**
      * Detect whether object data has been updated and return the updated state
      * */
	 public function get_state_change($new_data_state = null, $update_optional = null){
	 	 $simple_changed = [];
	 	 $fk_changed = [];
	 	 $nk_changed = [];
	 	 [$defined_field_names, $simple_fields, $fk_fields, $nk_fields] = $this->classify_fields();
	 	 $new_data_state = $new_data_state ?? $this->get_data_state();

	 	 foreach($new_data_state as $key => $val){
	 	 	 if($val != $this->data_state[$key] && ( !is_null($val) && $val != '')){ //This null and empty string condition must be rechecked!
	 	 	 	 if(in_array($key, $simple_fields)){
		 	 	 	 $simple_changed[$key] = $val;
		 	 	 }elseif(in_array($key, $fk_fields)){
		 	 	 	 $fk_changed[$key] = $val;
		 	 	 }elseif(in_array($key, $nk_fields)){
		 	 	 	 /**
		 	 	 	  * I am assuming this is many to many navigation key, this part must be reworked
		 	 	 	  * to accomodate many to one and one to one cases as well
		 	 	 	  * */
		 	 	 	 $current_values = $val['values'];
		 	 	 	 $former_values = $this->data_state[$key]['values'];

		 	 	 	 $added_values = array_diff($current_values, $former_values);
		 	 	 	 $removed_values = array_diff($former_values, $current_values);

		 	 	 	 $val['added'] = $added_values;
		 	 	 	 $val['removed'] = $removed_values;

		 	 	 	 $nk_changed[$key] = $val;
		 	 	 }
	 	 	 }
	 	 }
	 	 return [$simple_changed, $fk_changed, $nk_changed];
	 }

     /**
      * When attempting to save an updated object, this returns those properties
      * that are savable.
      * */
	 public function get_savable_values(){
	 	 $regular_fields      = [];
	 	 $manytomany_fields   = [];
	 	 $manytomany_throughs = [];
	 	 //acquire field classification
	 	 [$defined_field_names, $simple_fields, $fk_fields, $nk_fields] = $this->classify_fields();

	     foreach($defined_field_names as $field){
	     	 /**
	     	  * At this point the data has gone through the constructor, so we are confident the model
	     	  * has all the fields required to be saved.
              * */
			 $val = $this->$field;

		     if(in_array($field, $simple_fields)){
		     	 $regular_fields[$field] = $val;
	     	 }elseif(in_array($field, $nk_fields)){
	     	 	 $nk_field_vals  = [];
	     	 	 $relation       = $this->is_relation_field($field);
	     	 	 $through        = $relation->get_through_model();
	     	 	 $fmodel         = $relation->fmodel;
	     	 	 $fmodel_pk_name = $fmodel::state()->meta->pk_name;
	     	 	 foreach($val as $v){
	     	 	 	 $nk_field_vals[] = is_object($v) ? $v->$fmodel_pk_name : $v;
	     	 	 }

	     	 	 $manytomany_fields[$field]   = $nk_field_vals;
	     	 	 $manytomany_throughs[$field] = $through;
	     	 }elseif(in_array($field, $fk_fields)){
	     	 	 //will do later
	     	 	 $relation = $this->is_relation_field($field);
	     	 	 $fmodel         = $relation->fmodel;
	     	 	 $fmodel_pk_name = $fmodel::state()->meta->pk_name;
	     	 	 $regular_fields[$field] = is_object($val) ? $val->$fmodel_pk_name : $val;
	     	 }
	     }

	     return [$regular_fields, $manytomany_fields, $manytomany_throughs];
	 }

	 /**
	  * This save assumes the data was set via the model instructor.
	  * Notes on save:
	  * 1. By default only simple values are saved or updated when save is called.
	  * 2. Relation objects must already be existing in the database, as only their ids will be saved
	  * 3. Values for properties that were not explicitly defined i.e values generated by auto_cm_fields, auto_cmdt_fields, soft_delete and enable_multitenancy flags will be filled in automatically. If there are values assigned for these properties here, they will be ignored.
	  * */
	 public function save(?array $update_optional = null){
	 	 [$regular_fields, $manytomany_fields, $manytomany_throughs] = $this->get_savable_values();
	 	 $object_pk_name  = $this->meta->pk_name;
         $result = new Db($this->meta->db_class)->transaction([
	 	 	 /**
		 	  * Save the current object. 
		 	  * 
		 	  * This object may as well be already existing, but the models action_on_duplicate setting
		 	  * should take care of it.
		 	  * */
	 	 	 #[TransactionOutput('object1')]
	 	 	 function() use ($regular_fields){
	 	 	 	 return self::new($regular_fields)->save();
	 	     }, 

             //Save many to many objects
             #[TransactionOutput('object2')]
	 	     function($object1) use ($manytomany_fields, $manytomany_throughs, $object_pk_name){
	 	     	 if($manytomany_fields){
			 	 	 $object_pk_value = $object1->$object_pk_name;
			 	 	 foreach($manytomany_fields as $field_name => $field_values){
			 	 	 	 $through = $manytomany_throughs[$field_name];
			 	 	 	 $toadd = [];
			 	 	 	 foreach($field_values as $id){
			 	 	 	 	 $toadd[] = [$through[3] => $object_pk_value, $through[4] => $id];
			 	 	 	 }
			 	 	 	 $object1->$field_name = $through[1]::new($toadd)->save();
			 	 	 }
			 	 }
	 	     	 return $object1;
	 	     }
	 	 ]);

	 	 return $result['object1'];
	 }

	 public function jsonSerialize() : mixed {
	 	 $formatted_data = [];
	 	 foreach($this->data as $key => $val){
	 	 	 $formatted_data[$key] = $this->$key;
	 	 }
         return $formatted_data;
     }

     /**
      * Takes in a key => value array of data and converts all the keys to column names or field names
      * depending on the keytype setting.
      * 
      * @param array $data - the data to convert
      * @param string $keytype - fields or columns
      * 
      * */
     private function format_data(array $data, string $keytype = 'fields'){
     	 $formatted_values = [];
 	     $db_columns = $this->meta->column_names;
 	     $db_columns_flip = array_flip($db_columns);
	 	 foreach($data as $k => $v){
	 	 	 $field_name = null;
	 	 	 $col_name   = null;
	 	 	 if(array_key_exists($k, $db_columns)){
	 	 	 	 $field_name = $k;
	 	 	 	 $col_name = $db_columns[$k];
	 	 	 }

	 	 	 if(!$field_name && !$col_name){
	 	 	 	 if(array_key_exists($k, $db_columns_flip)){
	 	 	 	     $col_name = $k;
	 	 	 	     $field_name = $db_columns_flip[$k];
	 	 	     }
	 	 	 }

	 	 	 if(!$field_name && !$col_name){
	 	 	 	 $formatted_values[$k] = $v;
	 	 	 	 continue;
	 	 	 }

	 	 	 if($keytype === 'fields'){
	 	 	 	 $formatted_values[$field_name] = $v;
	 	 	 }else{
	 	 	 	 $formatted_values[$col_name] = $v;
	 	 	 }
	 	 }

	 	 return $formatted_values;
     }

     private function assert_required_fields(array $data){
     	 $required_fields = [];
     	 foreach($this->meta->fields as $f){
     	 	 if(!$f instanceof VirtualField && $f->required){
     	 	 	 /**
     	 	 	  * generate a value for primary key here if there is none
     	 	 	  * 
     	 	 	  * For integer primary keys, just assign 1. The database will assign the right pk value
     	 	 	  * */
     	 	 	 if($f instanceof Pk && (!array_key_exists($f->field_name, $data) || (array_key_exists($f->field_name, $data) && !$data[$f->field_name]))){
     	 	 	 	 $data[$f->field_name] = $this->meta->pk_type === 'GUID' ? $this->guid() : 1;
     	 	 	 }

     	 	 	 $required_fields[] = $f->field_name;
     	 	 }
     	 }
     	 $missing_fields = array_diff($required_fields, array_keys($data));

     	 if(!empty($missing_fields)){
     	 	 throw new MissingRequiredFieldsException('The values for the following required fields were not provided: '.implode(", ", $missing_fields));
     	 }

     	 return $data;
     }

     private function fill_defaults(array $data){
     	 foreach($this->meta->fields as $f){
     	 	 if(!$f instanceof VirtualField && $f instanceof RealField && !in_array($f->field_name, $this->meta->non_defined_field_names)){
     	 	 	 if(!array_key_exists($f->field_name, $data)){
     	 	 	 	 $data[$f->field_name] = $f->default;
     	 	 	 }
     	 	 }
     	 }

     	 return $data;
     }

     /**
      * Ensure that the keys of the data array are field names defined on the model.
      * Note: at this point even a column name instead of a field name will be disregarded.
      * 
      * Column names are the db representations of the model field names and field names 
      * are how the properties are named on the model itself.
      * 
      * @param array $data - the key => value data array
      * @param bool  $trict - if strict is true, an exception will be thrown where a key name is not a field defined on 
      *                       the model
      * */
     private function assert_correct_fields(array $data){
     	 $model_field_names = array_keys($this->meta->column_names);
     	 foreach(array_keys($data) as $dk){
     	 	 if(!in_array($dk, $model_field_names)){
     	 		 throw new UndefinedFieldException('The key: '.$dk.' is not a field on this model!');
     	 	 }
     	 }
     }

     private function run_data_validation(array $data, bool $partial = false){
     	 /**
     	  * Strip the data of keys the following:
     	  * 
     	  * 1. Keys that are not actual column names - These are navigational and virtual field names
     	  * 2. Primary key name - this does not need validation
     	  * 3. Foreign key names
     	  * 3. File keys whose valus are just strings (File names)
     	  * 4. Non defined field names - these are fields injected by auto_cm family of settings
     	  * 5. If partial, especially for updates, fields that are not in data
     	  * */
     	 $fields_to_validate = $this->meta->actual_column_names;
     	 unset($fields_to_validate[$this->meta->pk_name]);
     	 foreach($this->meta->file_field_names as $ffn){
	 	 	 if(isset($data[$ffn]) && !is_array($data[$ffn])){
	 	 	 	 unset($fields_to_validate[$ffn]);
	 	 	 }
	 	 }
	 	 foreach($this->meta->non_defined_field_names as $ndfn){
	 	 	 unset($fields_to_validate[$ndfn]);
	 	 }
	 	 foreach($this->meta->fk_field_names as $fkfn){
	 	 	 unset($fields_to_validate[$fkfn]);
	 	 }
	 	 if($partial){
	 	 	 $current_fields = array_keys($fields_to_validate);
	 	 	 foreach($current_fields as $cf){
	 	 	 	 if(!array_key_exists($cf, $data)){
	 	 	 	 	 unset($fields_to_validate[$cf]);
	 	 	 	 }
	 	 	 }
	 	 }

	 	 $validation_config = [];
	 	 $datato_validate   = [];
	 	 foreach($fields_to_validate as $ftv_key => $ftv){
	 	 	 $datato_validate[$ftv_key] = $data[$ftv_key];
	 	 	 $validation_config[$ftv_key] = $this->meta->fields[$ftv_key]->get_validation_configurations();
	 	 }

	 	 $validation_feedback = ModelValidator::validate($validation_config, $datato_validate);

	 	 if($validation_feedback["status"] !== 0){
			 throw new FieldValidationException([
			 	 'model'     => $this::class,
			 	 'operation' => '',
			 	 'dirty'     => $validation_feedback['dirty']
			 ]);
		 }
	 }

	 public function get_insert_data($request){
	 	 $fields_to_save = $this->meta->actual_column_names;
	 	 //$data_to_save   = array_intersect_key($this->data, array_flip($fields_to_save));
	 	 $data_to_save   = array_intersect_key($this->data, $fields_to_save);
	 	 $data_to_save   = $this->swap_properties_with_columns($data_to_save);
         
	 	 //Inject creator and modifier fields, created and modified date time fields and deleted fields
	 	 if($this->meta->auto_cm){
	 	 	 $data_to_save[$this->meta->created_by_field] = $request->user->user_id ?? 0; #Id of current user
	 	 	 $data_to_save[$this->meta->modified_by_field] = $request->user->user_id ?? 0; #Id of current user
	 	 }
	 	 if($this->meta->auto_cmdt){
	 	 	 $data_to_save[$this->meta->created_at_field] = time(); #current date time.
	 	 	 $data_to_save[$this->meta->modified_at_field] = time(); #Current date time
	 	 }
	 	 if($this->meta->soft_delete){
	 	 	 $data_to_save[$this->meta->deleted_field] = 0; #0 or 1, will be updated according to the operation
	 	 	 $data_to_save[$this->meta->deleted_by_field] = $request->user->user_id ?? 0; #Id of current user
	 	 	 $data_to_save[$this->meta->deleted_at_field] = time(); #current date and time stamp
	 	 }

	 	 $file_data = $this->prepare_file_data($data_to_save);

	 	 return [$data_to_save, $file_data];
	 }

	 public function get_update_data($data, $request, $data_state = null, $skip_validation = false){
	 	 //convert any values coming in with column names to field names
	 	 $data = $this->format_data($data);

         //ensure all the data keys are column names or fields defined on model
	 	 $this->assert_correct_fields($data);

         //run validation on the data
         $this->run_data_validation($data, true);

         //strip the primary key field, navigtaional and virtual fields, and the deleted field
	 	 unset($data[$this->meta->deleted_field]);
	 	 //unset($data[$this->meta->pk_name]);
	 	 $actual_fields = array_keys($this->meta->actual_column_names);

         $clean_data = [];
     	 foreach(array_keys($data) as $_dk){
     	 	 if(in_array($_dk, $actual_fields)){
     	 	 	 $clean_data[$_dk] = $data[$_dk];
     	 	 }
     	 }
     	 $data = $clean_data;

	 	 //Make sure data only uses db column names.
	 	 $data = $this->format_data($data, 'columns');

	 	 //Inject modifier and modified date time fields
	 	 if($this->meta->auto_cm){
	 	 	$data[$this->meta->modified_by_field] = $request->user->user_id ?? 0; #Id of current user
	 	 }
	 	 if($this->meta->auto_cmdt){
	 	 	 $data[$this->meta->modified_at_field] = time(); #Current date time
	 	 }

	 	 //Prepare file data.
	 	 $file_data = $this->prepare_file_data($data, $data_state);

	 	 return [$data, $file_data];
	 }

     //add new row(s) to database or batch create new instances
	 public static function new(array $data){
	 	 return new CreateManager(get_called_class(), $data);
	 }

	 //update exisitng rows (s)
	 public static function set(array $data){
	 	 return new UpdateManager(get_called_class(), $data);
	 }

	 //delete one or more rows
	 public static function del(){
	 	 return new DeleteManager(get_called_class());
	 }

	 //empty the entire table
	 public static function empty(){
	 	 return new TruncateManager(get_called_class());
	 }

	 //get one or more rows
	 public static function get($tablealiase = null, $tableref = null){
	 	 $calledclass = get_called_class();
	 	 [$dbclass, $tablename] = $calledclass::get_table_n_dbcontext();

	 	 return self::init_readmanager($tablename, $dbclass, $tablealiase, $tableref);
	 }

	 private static function init_readmanager($table, $dbclass, $tablealiase, $tableref){
	 	 $readmanager = new ReadManager();
	 	 $readmanager->initialize(table: $table, dbclass: $dbclass, tablealiase: $tablealiase, tableref: $tableref);
	 	 return $readmanager;
	 }

	 //run custom sql and data
	 public static function run(string $sql, string $operation, ?array $data = null, bool $multiple = true){
	 	 return new RunManager($sql, $operation, $data, $multiple);
	 }

     /**
      * In cases where the data is coming from the user, this method will be run before anything else happens.
      * 
      * Here is where the data is normalized or transformed into a format
      * acceptibe for the model
      * 
      * This client can override during the model definition, but must not
      * fail to call the parent's method.
      * */
	 protected function on_model_input(array &$kwargs){

	 }

     /**
      * During the construction of a model, especially one accepting data from
      * user, override this method to specify how relation fields may obtain their values
      * */
     protected function on_assign_relations(array &$kwargs){
     	 
     }

     protected function on_model_validate(array &$kwargs){
     	 
     }
}
