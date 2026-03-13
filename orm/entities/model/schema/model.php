<?php
namespace SaQle\Orm\Entities\Model\Schema;

use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\{Pk, TextField, OneToOne, OneToMany, FloatField, IntegerField, ManyToMany, FileField, DateField, TimeField, DateTimeField, TimestampField, BooleanField, VirtualField};
use SaQle\Core\Exceptions\Base\ValidationException;
use SaQle\Commons\StringUtils;
use SaQle\Orm\Entities\Model\Manager\{CreateManager, UpdateManager, DeleteManager, TruncateManager, ReadManager, RunManager};
use SaQle\Orm\Entities\Model\Interfaces\{IModel, ITableSchema};
use SaQle\Orm\Entities\Model\Collection\GenericModelCollection;
use SaQle\Core\Exceptions\Model\{UndefinedFieldException, MissingRequiredFieldsException};
use SaQle\Build\Utils\MigrationUtils;
use SaQle\Core\Files\{TempFileRef, UploadedFile, StoredFile, StoredFileCollection};
use SaQle\Core\Files\Storage\TempStorage;
use SaQle\Core\Assert\Assert;
use Exception;
use JsonSerializable;
use InvalidArgumentException;
use ReflectionClass;

abstract class Model implements ITableSchema, IModel, JsonSerializable {
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
     private array $data = [];

     /**
      * File references. Models will not have file objects. When one of the model fields
      * is a file field, and it has a value, this file will be saved to a temporrary location
      * after all the validation is done.
      * 
      * This is a key => value array of all the files, where the key is the field name
      * and the value is the temporary path, to be used when saving the file to its
      * final and permamnent destination later.
      * 
      * file_upload_session - a unique id generated per model instance. used when committing files later
      * references - the generated file references after temporary save
      * */
     private array $files = [
     	 'file_upload_session' => '',
     	 'references' => []
     ];

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
	 public mixed $table {
 	 	 get => $this->get_shared_meta(static::class);
	 }

	 protected static array $instances = [];
	 
	 /**
	  * Create a new model instance. A model instance can be created under these conditions.
	  * 1. From static, to provide access to model meta configuration.
	  * 2. From a database hydration.
	  * 3. By the client.
	  * 
	  * The kwargs argument provided is an associative array of field names and their values.
	  * */
	 public function __construct(...$kwargs){
	 	 /**
	 	  * if a model isntance is not created from static and from database,
	 	  * the field values must be provided in the constructor
	 	  * */
	 	 if(!self::$from_static_method && !self::$from_database){
	 	 	 $kwargs = $this->initialize_model_data($kwargs);
         }

         $this->data = $kwargs;
	 	 $this->readonly = self::$from_static_method;
         self::$from_static_method = false;
     }

     public function initialize_model_data(array $data, bool $return = true){
     	 Assert::isNonEmptyMap($data, "The data provided is not properly defined!");

         //convert columns to fields
     	 $data = $this->format_data($data);

         //ensure all the data keys are field names defined on model
 	 	 $this->assert_correct_fields($data);

 	 	 //fill in defaults for all the fields that haven't been provided
         $this->fill_defaults($data);

         //make sure data is provided for all the required fields
         $this->assert_required_fields($data);

         //run validation on the data
         $this->run_data_validation($data);

         //save files to temporary holding
         $this->save_model_files($data);

         if($return)
         	 return $data;

         $this->data = $data;
     }

     private function create_shared_meta(string $class_name){
     	 $table = new Table();
     	 $table->primary_key('id', config('model.pk_type')); //default primary key
         $table->set_table_defaults($class_name);
         $this->table_schema($table);
         self::$shared_meta[$class_name] = $table;
         $table->add_audit_fields();
         $table->clean_model_fields();
         $table->set_unique_constraints();
     }

     public function set_table_and_connection(?string $connection = null){
     	 [$connection_name, $table_name] = self::get_table_and_connection($connection);
     	 $this->table->set_table_and_connection($table_name, $connection_name);
     }

     //method to ensure shared meta per class
     private function get_shared_meta(string $class_name){
         //Check if meta already exists for this class
         if(!isset(self::$shared_meta[$class_name])){
         	 $this->create_shared_meta($class_name);
         }

         //return the shared meta for this class
         return self::$shared_meta[$class_name];
     }

     //abstract method that must be implemented by concrete classes to define fields
     abstract protected function table_schema(Table $table): void;

     //get the collection class for this model
     public static function collection_class() : string {
         return GenericModelCollection::class;
     }

     final public function get_data(){
     	 return $this->data;
     }

     final public function get_insert_data(){
     	 //remove navigation fields
     	 $nav_field_names = $this->table->get_nav_field_names();
     	 $insert_data = [];

     	 foreach($this->data as $key => $value){
     	 	 if(!in_array($key, $nav_field_names)){
     	 	 	 $insert_data[$key] = $value;
     	 	 }
     	 }

     	 return $insert_data;
     }

     final public function get_files(){
     	 return $this->files;
     }

     final public function get_file_references(){
     	 return $this->files['references'];
     }

     final public function get_upload_session(){
     	 return $this->files['file_upload_session'];
     }
     
     final public static function make(){
     	 self::$from_static_method = true;

         $called_class = get_called_class();
         if(!isset(self::$instances[$called_class])){
             self::$instances[$called_class] = new $called_class(...[]);
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

     final static public function hydrate_collection(array $objects) {

     	 $model_class = get_called_class();

     	 $models = [];

     	 foreach($objects as $obj){
     	 	 $models[] = $model_class::from_db(...get_object_vars($obj));
     	 }

     	 $collection_class = $model_class::collection_class();

 	 	 if($collection_class == GenericModelCollection::class){
 	 	 	 $collection = new GenericModelCollection($model_class, $models);
 	 	 }else{
 	 	 	 $collection = new $collection_class($models);
 	 	 }

 	 	 return $collection;
     }

	 public static function get_table_and_connection(?string $connection = null){
	 	 $connection = ($connection ?? config('db.default_connection')) ?? array_keys(config('db.connections'), [])[0] ?? '';

	 	 if(!$connection || !MigrationUtils::is_schema_defined($connection)){
	 	 	 throw new Exception("Please provide a valid database connection name!");
	 	 }

         $schema = $schema = config('db.schemas')[$connection];
         $schema_instance = new $schema();
         $models = $schema_instance->get_models();
         $model_classes = array_values($models);
         $index = array_search(static::class, $model_classes, true);
         if($index === false){
             throw new Exception(static::class . ": Not registered in '{$connection}' schema.");
         }

         return [$connection, array_keys($models)[$index]];
	 }

	 protected function save_model_files(array &$kwargs){

	 	 $file_field_names = $this->table->get_file_field_names();

	     if(empty($file_field_names)){
	         return;
	     }

	     $model = strtolower((new ReflectionClass($this))->getShortName());
	     $session = bin2hex(random_bytes(16));

	     foreach($file_field_names as $ffn){

	         if(!array_key_exists($ffn, $kwargs)){
	             continue;
	         }

	         $value = $kwargs[$ffn];

	         if($value === null){
	            continue;
	         }

             $field = $this->table->get_clean_fields()[$ffn];
	         $files = is_array($value) ? $value : [$value];
	         $refs  = [];

	         foreach($files as $file){
	             if(!$file instanceof UploadedFile) {
	                 throw new InvalidArgumentException(
	                    "Invalid upload supplied for file field '{$ffn}'"
	                 );
	             }

	             $refs[] = TempStorage::store(model: $model, field: $ffn, session: $session, file: $file);
	         }

             $kwargs[$ffn] = implode("~", array_map(fn($ref) => $ref->file_id, $refs));

	         $this->files['references'][$ffn] = count($refs) === 1 ? $refs[0] : $refs;
	     }

	     $this->files['file_upload_session'] = $session;
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
	 	 foreach($this->table->get_file_field_names() as $ffn){
	 	 	$db_col = $this->table->get_field_column_refs()[$ffn];
	 	 	#if the file exists.
	 	 	if(isset($clean_data[$db_col]) && is_array($clean_data[$db_col])){
	 	 		 $file_config = [
	 	 		 	 'crop_dimensions'   => $this->table->get_clean_fields()[$ffn]->crop_dimensions, 
	 	 		     'resize_dimensions' => $this->table->get_clean_fields()[$ffn]->resize_dimensions
	 	 		 ];

	 	 		 //rename files
	 	 		 $this->rename_uploaded_files($this->table->get_clean_fields()[$ffn], $clean_data, $db_col, $data_state);

	 	 		 //get the file path
	 	 		 $file_config['path'] = $this->table->get_clean_fields()[$ffn]->path($data_state ? $data_state : $clean_data);
	 			 $file_data[$db_col] = (Object)['file' => $clean_data[$db_col], 'config' => $file_config];

	 			 //reset the file value in clean data to file names only.
	     	     $clean_data[$db_col] = is_array($clean_data[$db_col]['name']) ? implode("~", $clean_data[$db_col]['name']) : $clean_data[$db_col]['name'];
	 	 	}
	 	 }
	 	 return $file_data;
	 }

     private function assert_field_exists(string $field_name, bool $strict = false) : void {
     	 if($strict){
     	 	 if(!array_key_exists($field_name, $this->table->get_clean_fields())){
     	 	     throw new Exception("The field: ".$field_name." does not exist on the model: ".$this::class);
     	     }
     	 }

     	 if(!array_key_exists($field_name, $this->table->get_clean_fields()) && !array_key_exists($field_name, $this->data)){
     	 	 throw new Exception("The field: ".$field_name." does not exist on the model: ".$this::class);
     	 }
     }

     private function render_field(string $name){
     	 $value = $this->data[$name] ?? "";

     	 if(array_key_exists($name, $this->table->get_clean_fields())){ 
     	 	 $field = $this->table->get_clean_fields()[$name];
     	 	 if($field instanceof FileField || is_subclass_of($field::class, FileField::class)){

     	 	 	 $default_url = $field->get_default_url();
     	 	 	 if(is_callable($default_url)){
     	 	 	 	 $default_url = $default_url($this);
     	 	 	 }

     	 	 	 if($field->get_multiple()){
     	 	 	 	$value = StoredFileCollection::from_json($value, $default_url);
     	 	 	 }else{
     	 	 	 	 $value = StoredFile::from_json($value);
     	 	 	 	 if(!$value && $default_url){
     	 	 	 	 	 $value = StoredFile::default($default_url);
     	 	 	 	 }
     	 	 	 }
     	 	 }

     	 	 $field->value($value);
     	 	 $value = $field->render_field($this);
     	 }

     	 return $value;
     }

	 public function __get($name){
	 	 $this->assert_field_exists($name);
	 	 return $this->render_field($name);
     }

     public function __set($name, $value){
     	 if($this->readonly){
             throw new Exception("This is a read only model. You cannot modify the values!");
         }

         $this->assert_field_exists($name);
         $this->data = array_merge($this->data, [$name => $value]);
     }

     public function __call(string $name, array $args){

     	 $this->assert_field_exists($name, true);
     	 $field = $this->table->get_clean_fields()[$name];
     	 $field->value($this->data[$name]);

     	 return $field;
     }

     public static function __callStatic(string $name, array $args){
     	 $model = self::make();
     	 if(!array_key_exists($name, $model->table->get_clean_fields())){
     	 	 throw new Exception("The field: ".$field_name." does not exist on the model: ".$model::class);
     	 }

     	 return $model->table->get_clean_fields()[$name];
     }

     /**
     * Return only specified fields from the model
     *
     * @param array $fields
     * @return array
     */
     public function only(array $fields): array {
         $result = [];

         foreach ($fields as $field) {
             if (isset($this->$field) || property_exists($this, $field)) {
                 $result[$field] = $this->$field;
             }
         }

         return $result;
     }

     /**
      * Check whether a field is a relation field defined on a model
      * and return the field or false otherwise
      * */
     public function is_relation_field(string $field){
	 	 $relation_field_names = array_merge($this->table->get_fk_field_names(), $this->table->get_nav_field_names());

	 	 if(!in_array($field, $relation_field_names)){
	 		 return false;
	 	 }

	 	 return $this->table->get_clean_fields()[$field] ?? false;
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
 	     $db_columns = $this->table->get_field_column_refs();
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

     /**
      * Ensure that values have been provided for all
      * fields that have required = true
      * */
     private function assert_required_fields(array $data){

     	 $required_fields = [];

     	 foreach($this->table->get_clean_fields() as $f){
     	 	 if(!in_array($f->get_name(), $this->table->get_defined_field_names())){
     	 	 	 continue;
     	 	 }

     	 	 if($f->is_required()){
     	 	 	 $required_fields[] = $f->get_name();
     	 	 }
     	 }

     	 $missing_fields = array_diff($required_fields, array_keys($data));

     	 if(!empty($missing_fields)){
     	 	 throw new MissingRequiredFieldsException('The values for the following required fields were not provided: '.implode(", ", $missing_fields));
     	 }
     }

     private function fill_defaults(array &$data){
     	
     	 foreach($this->table->get_clean_fields() as $f){
     	 	 if(!in_array($f->get_name(), $this->table->get_defined_field_names())){
     	 	 	 continue;
     	 	 }

     	 	 if(!array_key_exists($f->get_name(), $data)){
     	 	 	 if($f->is_primary()){
     	 	 	 	 $data[$f->get_name()] = $this->table->get_pk_type() === 'UUID' ? $this->guid() : 1;
     	 	 	 	 continue;
     	 	 	 }

     	 	 	 $data[$f->get_name()] = $f->get_default();
     	 	 }
     	 }

     	 $user_id = request()->user->user_id ?? null;

     	 //inject creator and modifier fields, created and modified date time fields and deleted fields
	 	 if($this->table->has_user_audit()){
	 	 	 $data['author'] = $user_id;
	 	 	 $data['modifier'] = $user_id;
	 	 }
	 	 if($this->table->has_timestamps()){
	 	 	 $data['created_at'] = time();
	 	 	 $data['modified_at'] = time();
	 	 }
	 	 if($this->table->has_soft_delete()){
	 	 	 $data['is_removed'] = 0;
	 	 	 $data['remover'] = null;
	 	 	 $data['removed_at'] = null;
	 	 }
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
     	 $model_field_names = array_keys($this->table->get_field_column_refs());
     	 foreach(array_keys($data) as $dk){
     	 	 if(!in_array($dk, $model_field_names)){
     	 		 throw new UndefinedFieldException('The key: '.$dk.' is not a field on this model!');
     	 	 }
     	 }
     }

     /**
      * Run validation on the data to ensure the model is in a valid
      * and correct state.
      * */
     private function run_data_validation(array $data, bool $partial = false){
     	 
     	 $errors = [];

     	 foreach($data as $field_name => $field_value){
     	 	 $field = $this->table->get_clean_fields()[$field_name];
     	 	 
     	 	 $validator = $field->validator();

     	 	 /*$result = $validator->validate($field_name, $field_value);

     	 	 if(!$result->isvalid){
     	 	 	 $errors[$field_name] = $result->errors;
             }*/
     	 }

     	 if(!empty($errors)){
             throw new ValidationException(context: [
                 'errors' => $errors
             ]);
         }
	 }

	 public function get_update_data($data, $request, $data_state = null, $skip_validation = false){
	 	 $column_refs = $this->table->get_table_column_names();

	 	 //convert any values coming in with column names to field names
	 	 $data = $this->format_data($data);

         //ensure all the data keys are column names or fields defined on model
	 	 $this->assert_correct_fields($data);

         //run validation on the data
         $this->run_data_validation($data, true);

         //strip the primary key field, navigtaional and virtual fields, and the deleted field
	 	 unset($data[$this->table->get_is_removed_column()]);
	 	 //unset($data[$this->table->get_pk_name()]);
	 	 $actual_fields = array_keys($column_refs);

         $clean_data = [];
     	 foreach(array_keys($data) as $_dk){
     	 	 if(in_array($_dk, $actual_fields)){
     	 	 	 $clean_data[$_dk] = $data[$_dk];
     	 	 }
     	 }
     	 $data = $clean_data;

     	 //Inject modifier and modified date time fields
	 	 if($this->table->has_user_audit()){
	 	 	$data['modifier'] = $request->user->user_id ?? null;
	 	 }
	 	 if($this->table->has_timestamps()){
	 	 	 $data['modified_at'] = time();
	 	 }

	 	 //Make sure data only uses db column names.
	 	 $data = $this->format_data($data, 'columns');

	 	 //Prepare file data.
	 	 $file_data = $this->prepare_file_data($data, $data_state);

	 	 return [$data, $file_data];
	 }

     //change the connection right before an operation
	 public static function using(string $connection){
	 	 $model_instance = self::make();
	 	 $model_instance->set_table_and_connection($connection);
         return new ModelProxy($model_instance);
     }

     //add new row(s) to database or batch create new instances
	 public static function create(array $data) : CreateManager {
	 	 $model_instance = new static(...$data);
	 	 $model_instance->set_table_and_connection();
	 	 return new CreateManager($model_instance);
	 }

     public static function create_many(array $data) : CreateManager {
         $model_class = get_called_class();
         $collection_class = $model_class::collection_class();

         //assert data is valid
         $collection_class::assert_valid_data($data);

         $converted_data = [];

         //convert the data to an array of models and set connection
         foreach($data as $d){

             if($d instanceof Model){
                 $d->set_table_and_connection();
                 $converted_data[] = $d;
                 continue;
             }

             if(is_array($d) || is_object($d)){
                 $model_instance = is_array($d) ? new $model_class(...$d) : new $model_class(...(array)$d);
                 $model_instance->set_table_and_connection();
                 $converted_data[] = $model_instance;
             }
         }
         
         if($collection_class == GenericModelCollection::class){
             $collection = new GenericModelCollection($model_class, $converted_data);
         }else{
             $collection = new $collection_class($converted_data);
         }

         return new CreateManager($collection);
     }

	 //update exisitng rows (s)
	 public static function update(array $data){
	 	 $model_instance = self::make();
	 	 $model_instance->set_table_and_connection();
	 	 return new UpdateManager($model_instance, $data);
	 }

	 //delete one or more rows
	 public static function delete(bool $permanently = false){
	 	 $model_instance = self::make();
	 	 $model_instance->set_table_and_connection();
	 	 return new DeleteManager($model_instance, $permanently);
	 }

	 //empty the entire table
	 public static function empty(){
	 	 $model_instance = self::make();
	 	 $model_instance->set_table_and_connection();
	 	 return new TruncateManager($model_instance);
	 }

	 //get one or more rows
	 public static function get($tablealiase = null, $tableref = null){
	 	 $model_instance = self::make();
	 	 $model_instance->set_table_and_connection();
	 	 return new ReadManager($model_instance, $tablealiase, $tableref);
	 }

	 //run custom sql and data
	 public static function run(string $sql, string $operation, ?array $data = null, bool $multiple = true){
	 	 $model_instance = self::make();
         $model_instance->set_table_and_connection();
	 	 return new RunManager($model_instance, $sql, $operation, $data, $multiple);
	 }

     public static function get_fillable_fields(){
     	 $instance = self::make();
     	 
     	 /**
     	  * Strip the actual colums of the following:
     	  * 
     	  * 1. Primary key name - must not be manually filled
     	  * 2. Non defined field names - values will be added automatically
     	  *
     	  * */
     	 $field_names = $instance->table->get_defined_field_names();

     	 //remove the primary key
     	 $pk_index = array_search($instance->table->get_pk_name(), $field_names);
     	 unset($field_names[$pk_index]);

     	 //remove automaticly added fields
	 	 foreach($instance->table->get_audit_field_names() as $ndfn){
	 	 	 $ndf_index = array_search($ndfn, $field_names);
	 	 	 unset($field_names[$ndf_index]);
	 	 }

	 	 //remove virtual fields
	 	 foreach($instance->table->get_virtual_field_names() as $vfn){
	 	 	 $vfn_index = array_search($vfn, $field_names);
	 	 	 unset($field_names[$vfn_index]);
	 	 }

	 	 $fillable = [];

	 	 foreach($instance->get_fields() as $field){
	 	 	 if(in_array($field->field_name, $field_names)){
	 	 	 	 $fillable[$field->field_name] = $field->default ?? null;
	 	 	 }
	 	 }

     	 return $fillable;
     }


     /**
      * Utility methods.
      * 
      * I am adding this so that I dont expose internal properties
      * to the outside for easy maintainance
      * */

     private static function get_model_setup(){
     	 $model = get_called_class();
     	 $model_instance = $model::make();
     	 return $model_instance->table;
     }

     //get all the model fields
     public static function get_fields(){
         return self::get_model_setup()->get_clean_fields();
     }

     //get the primary key name
     public static function get_pk_name(){
     	 return self::get_model_setup()->get_pk_name();
     }

     //get the primary key type
     public static function get_pk_type(){
     	 return self::get_model_setup()->get_pk_type();
     }

     //get model name property
     public static function get_name_property(){
     	 return self::get_model_setup()->name_property;
     }

     //get all the column names
     public static function get_column_names(){
     	 return self::get_model_setup()->get_field_column_refs();
     }
	 
	 //get actual column names
	 public static function get_table_column_names(){
	    return self::get_model_setup()->get_table_column_names();
	 }

     //get all the defined field names
     public static function get_defined_field_names(){
     	 return self::get_model_setup()->get_defined_field_names();
     }

     //get all the unique fields
     public static function get_unique_field_names(){
     	 return self::get_model_setup()->get_unique_field_names();
     }
	 
	 //return navigation field names
	 public static function get_nav_field_names(){
	     return self::get_model_setup()->get_nav_field_names();
	 }

	 //return is temporary
	 public static function is_temporary(){
	 	 return self::get_model_setup()->is_temporary();
	 }

	 //has soft delete
	 public static function has_soft_delete(){
	 	 return self::get_model_setup()->has_soft_delete();
	 }

	 //get action on duplicate
	 public static function get_action_on_duplicate(){
	 	 return self::get_model_setup()->get_action_on_duplicate();
	 }

	 //get file required fileds
	 public static function get_file_required_fields(){
	 	 return self::get_model_setup()->get_file_required_fields();
	 }

	 //get unique constraints
	 public static function get_unique_constraints(){
	 	 return self::get_model_setup()->get_unique_constraints();
	 }

	 public function get_connection_name(){
	 	 return $this->table->get_connection_name();
	 }

	 public function get_table_name(){
	 	 return $this->table->get_table_name();
	 }

     //get all the models that belong to the same schema as this one
	 public function get_sibling_models(){
	 	 $schema = config('db.schemas')[$this->table->get_connection_name()];
	 	 return new $schema()->get_models();
	 }

	 public function get_update_columns(){
	 	 $exclude_fields = array_merge(
	 	 	 $this->table->get_unique_field_names(),
	 	 	 [$this->table->get_pk_name()],
	 	 	 $this->table->get_audit_field_names()
	 	 );

	 	 $update_fields = array_diff(array_keys($this->table->get_clean_fields()), $exclude_fields);
	 	 $update_columns = [];

	 	 foreach($update_fields as $f){
	 	 	 $update_columns[] = $this->table->get_field_column_refs()[$f];
	 	 }

	 	 return $update_columns;
	 }

}
