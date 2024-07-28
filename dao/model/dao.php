<?php
namespace SaQle\Dao\Model;

use SaQle\Dao\Field\Interfaces\IValidator;
use SaQle\Dao\Field\Relations\Interfaces\IRelation;
use SaQle\Http\Request\Request;
use SaQle\Services\Container\ContainerService;
use SaQle\Services\Container\Cf;
use SaQle\Dao\Model\Attributes\{CreatorModifierFields, CreateModifyDateTimeFields, SoftDeleteFields};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\Attributes\{PrimaryKey, ForeignKey, NavigationKey, TextFieldValidation, NumberFieldValidation, FileFieldValidation, FileConfig};
use SaQle\Dao\Field\Types\{Pk, TextField, OneToOne, OneToMany, FloatField, IntegerField, ManyToMany, FileField};
use SaQle\Dao\Field\Exceptions\FieldValidationException;
use SaQle\Security\Models\ModelValidator;
use SaQle\Commons\StringUtils;
use SaQle\Dao\Field\Types\Base\Relation;
use SaQle\Dao\Model\Manager\ModelManager;
use SaQle\Dao\Model\Interfaces\{IModel, IThroughModel};

abstract class Dao implements IModel{
	 use StringUtils;
	 /**
	  * Current request. This must be removed in future.
	  * */
	 protected Request $request;

	 /**
	 	 * This is key => val array of data access object meta data with the following optional keys
	 	 * @var string name_property : the property from which a value object deroves its name
	 	 * @var bool   auto_cm_fields: This tells the model to include author and modifier fields
	 	 * @var bool   auto_cmdt_fields: This tells the nodel to include the datetime(created, modified) fields
	 	 * @var bool   soft_delete: This tells the model to include a deleted field to enable soft delets
	 	 * @var string db_table: This is the name of the database table associated with model.
	 	 * @var string db_class: This is the name of the database context class associated with model.
	 	 * @var string pk_name: The primary key name
	 	 * @var string pk_type: The type of the primary key
	 	 * @var string created_by_field: The name of the created by field
	 	 * @var string created_at_field: The name of the created at field
	 	 * @var string modified_by_field: The name of the modified by field
	 	 * @var string modified_at_field: The name of the modified at field
	 	 * @var string deleted_by_field: The name of the deleted by field
	 	 * @var string deleted_at_field: The name of the deleted at field
	 	 * @var bool   deleted_field: The name of the deletedfield.
	 	 * */
	 private array $meta = [];

	 public function __construct(...$kwargs){
	 	 $this->initialize();
	 	 //print_r($this->meta);
	 }
     
     /**
      * Set the request property for dao
      * @param Request request
      * */
	 public function set_request(Request $request){
	 	$this->request = $request;
	 }

     /**
      * Set dao meta data as explained above
      * */
	 public function set_meta(array $meta){
     	$this->meta = array_merge($this->meta, $meta);
     }

     /**
      * Get the request
      * */
	 public function get_request(){
	 	return $this->request;
	 }

     /**
      * Get dao meta data
      * */
	 public function meta(){
	 	return $this->meta;
	 }


	 //utilities

	 /**
	  * Get the primary key name
	  * */
	 public function get_pk_name(){
	 	return $this->meta['pk_name'];
	 }

	 /**
	  * Get the primary key type
	  * */
	 public function get_pk_type(){
	 	return $this->meta['pk_type'];
	 }

     /**
      * Return the dao name property as set in the meta data
      * */
	 public function get_name_property(){
	 	 return array_key_exists("name_property", $this->meta) ? $this->meta['name_property'] : "";
	 }

	 /**
      * Return the name of the database table associated with model
      * */
	 public function get_db_table(){
	 	 return $this->meta['db_table'];
	 }

	 /**
	  * Return the auto_cm_fields property as set in the meta data
	  * */
	 public function get_auto_cm(){
	 	 return array_key_exists("auto_cm_fields", $this->meta) ? $this->meta['auto_cm_fields'] : false;
	 }

	 /**
	  * Return the auto_cmdt_fields property as set in the meta data
	  * */
	 public function get_auto_cmdt(){
	 	 return array_key_exists("auto_cmdt_fields", $this->meta) ? $this->meta['auto_cmdt_fields'] : false;
	 }

	 /**
	  * Return the soft_delete property as set in the meta data
	  * */
	 public function get_soft_delete(){
	 	 return array_key_exists("soft_delete", $this->meta) ? $this->meta['soft_delete'] : false;
	 }

	 /**
	  * Get navigation field names
	  * */
	 public function get_navigation_field_names(){
	 	return $this->meta['nav_field_names'];
	 }

	 /**
	  * Get foerign key field names
	  * */
	 public function get_fk_field_names(){
	 	return $this->meta['fk_field_names'];
	 }

	 /**
	  * the names of all the fields
	  * */
	 public function get_field_names(){
	 	return array_keys($this->meta['fields']);
	 }

	 /**
	  * Get all the fields.
	  * */
	 public function get_all_fields(){
	 	return $this->meta['fields'];
	 }

	 /**
	  * Get all non navigational fields
	  * */
	 public function get_all_nonnav_fields(){
		 $fields = [];
		 foreach($this->meta['fields'] as $fk => $f){
		 	 if($f instanceof Relation){
		 	 	 $is_navigation = $f->is_navigation();
		 	 	 if(!$is_navigation){
		 	 	 	 $fields[$fk] = $f;
		 	 	 }
		 	 }else{
		 	 	 $fields[$fk] = $f;
		 	 }
		 }
	 	 return $fields;
	 }

	 /**
	  * Get auto cm fields.
	  * */
	 public function get_auto_cm_fields(){
	 	 return [$this->meta['created_by_field'], $this->meta['modified_by_field']];
	 }

	 /**
	  * Get auto cmdt fields
	  * */
	 public function get_auto_cmdt_fields(){
	 	return [$this->meta['created_at_field'], $this->meta['modified_at_field']];
	 }

	 /**
	  * Get soft delete fields
	  * */
	 public function get_soft_delete_fields(){
	 	return [$this->meta['deleted_field'], $this->meta['deleted_by_field'], $this->meta['deleted_at_field']];
	 }

	 public static function get_table_n_dbcontext(){
	 	 $db_class = false;
	 	 $table_name = null;
	 	 $current_model_name = get_called_class();
	 	 $Interfaces = class_implements($current_model_name);
	 	 
	 	 /**
	 	  * Note:
	 	  * For regular models defined by the user, the model is expected to have been registered with
	 	  * one or more database contexts.
	 	  * 
	 	  * For through models generated by makemigations command, the model may not be 
	 	  * registered with any databasecontext, therefore it will use the contexts of one of the related models.
	 	  * */
	 	 if(in_array(IThroughModel::class, $Interfaces)){
	 	 	 $related_models = $current_model_name::get_related_models();
	 	 	 if(!$related_models){
	 	 	 	throw new \Exception("A through model must have at least two related models!");
	 	 	 }

	 	 	 $first_model = $related_models[0];
	 	 	 [$db_class, $table_name] = $first_model::get_table_n_dbcontext();
	 	 }else{
	 	 	 $context_classes = array_keys(DB_CONTEXT_CLASSES);
		 	 for($x = 0; $x < count($context_classes); $x++){
		 	 	$context_class_name = $context_classes[$x];
		 	 	$models = $context_class_name::get_models();
		 	 	$model_classes = array_values($models);
		 	 	if(in_array($current_model_name, $model_classes)){
		 	 		$db_class = $context_class_name;
		 	 		$table_name = array_keys($models)[array_search($current_model_name, $model_classes)];
		 	 		break;
		 	 	}
		 	 }
	 	 } 

	 	 if(!$db_class || !$table_name){
	 	 	throw new \Exception($current_model_name.": Model not registered with any db contexts!");
	 	 }
	 	 return [$db_class, $table_name];
	 }

	 private function initialize(){
	 	 /**
	 	  * Inspect the class and initialize state based on the configurations
	 	  * declared on class and field attributes, or the fields themeselevs.
	 	  * 
	 	  * All state data will be stored in the meta property in a key => value
	 	  * fashion as explained in meta above.$p->getAttributes(NavigationKey::class)
	 	  * */

         [$db_class, $table_name] = self::get_table_n_dbcontext();
	 	 $this->meta['db_table'] = $table_name;
	 	 $this->meta['db_class'] = $db_class;

	 	 $reflector = new \ReflectionClass($this);

	 	 /**
	 	  * Check CreatorModifierFields attribute on class
	 	  * */
         $cmattributes = $reflector->getAttributes(CreatorModifierFields::class);
         $this->meta['auto_cm_fields'] = $cmattributes ? true : false;
         $this->meta['created_by_field'] = 'added_by'; #Override by calling set meta
         $this->meta['modified_by_field'] = "modified_by"; #Override by calling set_meta

         /**
          * Check CreateModifyDateTimeFields attribute on class
          * */
         $cmdtattributes = $reflector->getAttributes(CreateModifyDateTimeFields::class);
         $this->meta['auto_cmdt_fields'] = $cmdtattributes ? true : false;
         $this->meta['created_at_field'] = 'date_added'; #Override by calling set_meta
         $this->meta['modified_at_field'] = 'last_modified'; #Override by calling set_meta

         /**
          * Check SoftDeleteFields attribute on class
          * */
         $delattributes = $reflector->getAttributes(SoftDeleteFields::class);
         $this->meta['soft_delete'] = $delattributes ? true : false;
         $this->meta['deleted_by_field'] = 'deleted_by'; #Override by calling set_meta;
         $this->meta['deleted_at_field'] = 'deleted_at'; #Override by calling set_meta;
         $this->meta['deleted_field'] = 'deleted'; #Overrideby calling set_meta;

         $this->meta['fields'] = [];
         $this->meta['nav_field_names'] = [];
         $this->meta['fk_field_names'] = [];
         $this->meta['file_field_names'] = [];
         $properties = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
         for($pc = 0; $pc < count($properties); $pc++){
         	 $p = $properties[$pc];
		 	 
		 	 /**
		 	  * Property types come with a ? if declared nullable, remove it.
		 	  * */
			 $property_type = str_replace("?", "", $p->getType()); 
			 $property_name = $p->getName();

			 $rp = new \ReflectionProperty($this::class, $property_name);
			 if( !$rp->isInitialized($this) && $property_type === IField::class ){
			 	 continue;
			 }

			 /**
			  * Modern models have properties of type IField.
			  * */
			 $pinstance = $property_type === IField::class ? $p->getValue($this) : $this->convert_property_from_classic_to_modern($p, $property_type);
			 if($pinstance){
				 //set internal field name.
			 	 $pinstance->set_property_name($property_name);
			 	 //set the name of the class of field model.
				 $pinstance->set_model_class($this::class);
				 if($pinstance instanceof Pk){
			 	 	$this->meta['pk_name'] = $property_name;
			 	 	$this->meta['pk_type'] = $pinstance->get_key_type();
			 	 }elseif($pinstance instanceof OneToOne || $pinstance instanceof OneToMany || $pinstance instanceof ManyToMany){
			 	 	 if($pinstance->is_navigation()){
			 	 	 	$this->meta['nav_field_names'][] = $property_name;
			 	 	 }else{
			 	 	 	$this->meta['fk_field_names'][] = $property_name;
			 	 	 }
			 	 }elseif($pinstance instanceof FileField){
			 	 	$this->meta['file_field_names'][] = $property_name;
			 	 }
			 	 $this->meta['fields'][$property_name] = $pinstance;
			 }
			 
		 }

		 foreach($this->meta['fields'] as $mf){
		 	 //set the model class pk name
			 $mf->set_model_class_pk($this->meta['pk_name']);
			 //initialize state
			 $mf->initialize();
		 }
	 }

     private function translate_relation_config(array $classic){

     	/**
     	 * Check that all required keys are provided, otherwise return false.
     	 * */
     	$required = ['pdao', 'fdao', 'field', 'pfkeys'];
     	$all_provided = true;
     	foreach($required as $r){
     		 if(!isset($classic[$r])){
     		 	$all_provided = false;
     		 }
     	}
     	if(!$all_provided){
     		return false;
     	}

     	$modern = [
     		'pdao' => $classic['pdao'],
     		'fdao' => $classic['fdao'],
     		'multiple' => $classic['multiple'] ?? false,
     		'field' => $classic['field'],
     		'eager' => $classic['include'] ?? false,
     	];
     	$pfkeys = explode("=>", $classic['pfkeys']);
     	$modern['pk'] = $pfkeys[0];
     	$modern['fk'] = $pfkeys[1];
     	return $modern;
     }

	 private function convert_property_from_classic_to_modern($p, $type){
	 	 $is_primary = false;
	 	 $is_foreign = false;
	 	 $is_navigation = false;
	 	 $is_text = false;
	 	 $is_number = false;
	 	 $is_file = false;
	 	 $is_multiple = false;
	 	 $primary_type = 'GUID';
	 	 $aggregate_props = ['reverse' => true];
	 	 /**
	 	  * Check if property has a PrimaryKey attribute
	 	  * */
	 	 $pkattributes = $p->getAttributes(PrimaryKey::class);
	 	 if($pkattributes){
	 	 	$is_primary = true;
	 	 	$primery_type = $pkattributes[0]->getArguments()['type'] ?? 'GUID';
	 	 }
	 	 
	 	 /**
	 	  * Check if property has a ForeignKey attribute
	 	  * */
	 	 $fkattributes = $p->getAttributes(ForeignKey::class);
	 	 if($fkattributes){
	 	 	$fkparams = $this->translate_relation_config($fkattributes[0]->getArguments());
	 	 	if($fkparams === false){
	 	 		return null;
	 	 	}

	 	 	$is_foreign = true;
	 	 	$is_multiple = ($fkattributes[0]->newInstance())->get_multiple();
	 	 	$aggregate_props = array_merge($aggregate_props, $fkparams);
	 	 }

	 	 /**
	 	  * Check if property has a NavigationKey attribute
	 	  * */
	 	 $nkattributes = $p->getAttributes(NavigationKey::class);
	 	 if($nkattributes){
	 	 	$nkparams = $this->translate_relation_config($nkattributes[0]->getArguments());
	 	 	if($nkparams === false){
	 	 		return null;
	 	 	}

	 	 	$is_navigation = true;
	 	 	$is_multiple = ($nkattributes[0]->newInstance())->get_multiple();
	 	 	$aggregate_props = array_merge($aggregate_props, $nkparams);
	 	 }

	 	 /**
	 	  * Check if property has TextFieldValidation attribute.
	 	  * */
	 	 $tfattributes = $p->getAttributes(TextFieldValidation::class);
	 	 if($tfattributes){
	 	 	$is_text = true;
	 	 	$aggregate_props = array_merge($aggregate_props, $tfattributes[0]->getArguments());
	 	 }

	 	 /**
	 	  * Check if property has NumberFieldValidation attribute.
	 	  * */
	 	 $nfattributes = $p->getAttributes(NumberFieldValidation::class);
	 	 if($nfattributes){
	 	 	$is_number = true;
	 	 	$aggregate_props = array_merge($aggregate_props, $nfattributes[0]->getArguments());
	 	 }

	 	 /**
	 	  * Check if property has FileFieldValidation attribute.
	 	  * */
	 	 $ffattributes = $p->getAttributes(FileFieldValidation::class);
	 	 if($ffattributes){
	 	 	$is_file = true;
	 	 	$aggregate_props = array_merge($aggregate_props, $ffattributes[0]->getArguments());
	 	 	/**
	 	      * Check if property has FileConfig attribute.
	 	     * */
	 	 	$cfattributes = $p->getAttributes(FileConfig::class);
	 	 	if($cfattributes){
	 	 		 $aggregate_props = array_merge($aggregate_props, $cfattributes[0]->getArguments());
	 	 	}
	 	 }

	 	 /**
	 	  * Convert the field.
	 	  * */
	 	 if($is_primary){
	 	 	return new Pk($primary_type, ...$aggregate_props);
	 	 }

	 	 if($is_foreign || $is_navigation){
	 	 	if($is_navigation){
	 	 		$aggregate_props['isnav'] = true;
	 	 	}
	 	 	if($is_multiple){
	 	 		$aggregate_props['multiple'] = true;
	 	 	}
	 	 	return $is_multiple ? new OneToMany(...$aggregate_props) : new OneToOne(...$aggregate_props);
	 	 }

	 	 if($is_text){
	 	 	return new TextField(...$aggregate_props);
	 	 }

	 	 if($is_number){
	 	 	return $type == "int" ? new IntegerField(...$aggregate_props) : new FloatField(...$aggregate_props);
	 	 }

	 	 if($is_file){
	 	 	return new FileField(...$aggregate_props);
	 	 }

	 	 return null;
	 }

	 public static function db(){
	 	 [$db_class, $table_name] = self::get_table_n_dbcontext();
	 	 $manager = Cf::create(ContainerService::class)->createContextModelManager($db_class);
         $manager->initialize($table_name, $db_class);
         return $manager;
	 }

     public function get_validation_configurations(?array $field_names = null){
     	 $vc = [];
     	 foreach($this->meta['fields'] as $fn => $fv){
     	 	 if(!$field_names || ($field_names && in_array($fn, $field_names)) ){
     	 	 	 $fvc = $fv->get_validation_configurations();
	     	 	 if($fvc){
	     	 	 	 $vc[$fn] = $fvc;
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
     	 $tmp_data = [];
     	 $model_field_names = array_keys($this->meta['fields']);
     	 foreach(array_keys($data) as $_dk){
     	 	if(in_array($_dk, $model_field_names) && $_dk != $this->meta['pk_name'] && !in_array($_dk, $this->meta['nav_field_names'])){
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
	 	 foreach($this->meta['file_field_names'] as $ffn){
	 	 	if(isset($data[$ffn]) && !is_array($data[$ffn])){
	 	 		$file_names_only[$ffn] = $data[$ffn];
	 	 	}
	 	 }

	 	 $exclude = array_merge(array_keys($file_names_only), $this->meta['nav_field_names']);
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
		 return array_merge($validation_feedback['clean'], $file_names_only);
	 }

     private function rename_uploaded_files(&$clean_data, $file_key, $rename_callback){
	 	$original_clean_data = $clean_data;
	 	if(is_array($clean_data[$file_key]['name'])){
             foreach($clean_data[$file_key]['name'] as $n_index => $n){
                 $clean_data[$file_key]['name'][$n_index] = $this->$rename_callback((Object)$original_clean_data, $clean_data[$file_key]['name'][$n_index], $n_index);
             }
         }else{
             $clean_data[$file_key]['name'] = $this->$rename_callback((Object)$original_clean_data, $clean_data[$file_key]['name']);
         }
	 }

     public function get_file_configurations(){
     	 $fc = [];
     	 foreach($this->meta['file_field_names'] as $ffn){
     	 	$fc[$ffn] = $this->meta['fields'][$ffn]->get_field_attributes();
     	 }
     	 return $fc;
     }

	 private function prepare_file_data(&$clean_data){
	 	 /**
	 	  * Prepare files data: Only the file names will be saved in db.
	 	  * 1. Rename the files using the rename callback that was provided on the model if any
	 	  * 2. Get the file path using the path callback that was provided on the model if any.
	 	  * 3. Rest the file values in clean data to file names only
	 	  * */
	 	 $file_data = [];
	 	 foreach($this->meta['file_field_names'] as $ffn){
	 	 	#if the file exists.
	 	 	if(isset($clean_data[$ffn]) && is_array($clean_data[$ffn])){
	 	 		 $file_config = $this->meta['fields'][$ffn]->get_field_attributes();

	 	 		 //rename files if a rename callback was provided.
	 	 		 if(isset($file_config[$ffn]['rename_callback']) && method_exists($this, $file_config[$ffn]['rename_callback'])){
	 	 		 	 $rename_callback = $file_config[$ffn]['rename_callback'];
	 	 		 	 $this->rename_uploaded_files($clean_data, $file_key, $rename_callback);
	 	 		 }

	 	 		 //get the file path
     	 		 $folder_path = $file_config[$ffn]['path'] ?? "";
		         if($folder_path && method_exists($this, $folder_path)){
	 				 $folder_path = $this->$folder_path((Object)$clean_data);
	 			 }else{
	 			 	 $folder_path = ""; //there should be a default folder path to the media folder
	 			 }
	 			 $file_config[$ffn]['path'] = $folder_path;
	 			 $file_data[$ffn] = (Object)['file' => $clean_data[$ffn], 'config' => $file_config[$ffn]];

	 			 //reset the file value in clean data to file names only.
	     	     $clean_data[$ffn] = is_array($clean_data[$ffn]['name']) ? implode("~", $clean_data[$ffn]['name']) : $clean_data[$ffn]['name'];
	 	 	}
	 	 }
	 	 return $file_data;
	 }

     private function swap_properties_with_columns($clean_data){
     	 $swapped = [];
     	 foreach($clean_data as $pk => $pv){
     	 	 $ck = $this->meta['fields'][$pk]->get_db_column_name();
     	 	 $swapped[$ck] = $pv;
     	 }
     	 return $swapped;
     }

	 public function prepare_insert_data($data){
	 	 /**
	 	  * Get validated data.
	 	  * */
	 	 $clean_data = $this->get_clean_data($data, "insert");

	 	 /**
	 	  * Get file data if any.
	 	  * */
	 	 $file_data = $this->prepare_file_data($clean_data);

	 	 /**
	 	  * Generate GUID if the primary key is of that type.
	 	  * */
	 	 if($this->meta['pk_type']){
	 	 	$clean_data[$this->meta['pk_name']] = $this->guid();
	 	 }

	 	 /**
		  * Make sure the keys in in clean data represent column names of the table associated with dao
		  * and not property names.
		  * */
	 	 $clean_data = $this->swap_properties_with_columns($clean_data);

	 	 //print_r($clean_data);

	 	 /**
	 	  * Inject creator and modifier fields, created and modified date time fields and deleted
	 	  * fields
	 	  * */
	 	 if($this->meta['auto_cm_fields']){
	 	 	$clean_data[$this->meta['created_by_field']] = 0; #Id of current user
	 	 	$clean_data[$this->meta['modified_by_field']] = 0; #Id of current user
	 	 }
	 	 if($this->meta['auto_cmdt_fields']){
	 	 	 $clean_data[$this->meta['created_at_field']] = time(); #current date time.
	 	 	 $clean_data[$this->meta['modified_at_field']] = time(); #Current date time
	 	 }
	 	 if($this->meta['soft_delete']){
	 	 	$clean_data[$this->meta['deleted_field']] = 0; #0 or 1, will be updated according to the operation
	 	 	/*$clean_data[$this->meta['deleted_by_field']] = 0; #Id of current user
	 	 	$clean_data[$this->meta['deleted_at_field']] = 0; #current date and time stamp*/
	 	 }

	 	 return [$clean_data, $file_data];
	 }

	 public function prepare_update_data($data){
	 	 $clean_data = $this->get_clean_data($data, "update");

	 	 /**
	 	  * For models supporting soft delete, the deleted field(Whatever name its called in meta),
	 	  * must not be updated from here.
	 	  * */
	 	 unset($clean_data[$this->meta['deleted_field']]);

	 	 /**
	 	  * Inject modifier and modified date time fields
	 	  * */
	 	 if($this->meta['auto_cm_fields']){
	 	 	$clean_data[$this->meta['modified_by_field']] = 0; #Id of current user
	 	 }
	 	 if($this->meta['auto_cmdt_fields']){
	 	 	 $clean_data[$this->meta['modified_at_field']] = time(); #Current date time
	 	 }

	 	 /**
	 	  * Prepare file data.
	 	  * */
	 	 $file_data = $this->prepare_file_data($clean_data);

	 	 return [$clean_data, $file_data];
	 }

	 public function get_field_definitions(){
	 	 $defs = [];
     	 foreach($this->meta['fields'] as $fn => $fv){
     	 	 $fd = $fv->get_field_definition();
     	 	 if($fd){
     	 	 	 $defs[] = $fd;
     	 	 }
     	 }
     	 return $defs;
	 }

	 public function get_auto_include(){
	 	$auto_includes = [];
	 	foreach($this->meta['fields'] as $fn => $fv){
	 		if($fv instanceof Relation && $fv->is_eager()){
	 			$auto_includes[] = $fv->get_relation();
	 		}
	 	}
	 	return $auto_includes;
	 }

	 public function is_include(string $field){
	 	$includes = array_merge($this->meta['fk_field_names'], $this->meta['nav_field_names']);
	 	if(!in_array($field, $includes))
	 		return false;

	 	$instance = $this->meta['fields'][$field] ?? null;
	 	if(!$instance)
	 		return false;

	 	return $instance->get_relation();
	 }

	 /**
	  * Check that a model has a relationship with another model defined on one of the fields
	  * and return the relation.
	  * */
	 public function has_relationship_with(string $model_class){
	 	 $relation = false;
         $relation_fields = array_merge($this->meta['nav_field_names'], $this->meta['fk_field_names']);
         for($f = 0; $f < count($relation_fields); $f++){
         	 $field = $this->meta['fields'][$relation_fields[$f]];
         	 if($field->get_relation()->get_fdao() == $model_class){
         	 	$relation = $field;
         	 }
         }
         return $relation;
	 }

	 public function has_manytomany_relationship_with(string $model_class){
	 	 $relation = false;
         for($f = 0; $f < count($this->meta['nav_field_names']); $f++){
         	 $field = $this->meta['fields'][$this->meta['nav_field_names'][$f]];
         	 if($field->get_relation()->get_fdao() == $model_class && $field instanceof ManyToMany){
         	 	$relation = $field;
         	 }
         }
         return $relation;
	 }

     /**
      * Return daos class name,
      * 
      * @param bool include_namespace;
      * */
	 public function get_class_name(bool $include_namespace = false){
	 	 if($include_namespace)
	 		 return $this::class;
	 	 $nameparts = explode("\\", $this::class);
	 	 return end($nameparts);
	 }

	 /**
      * Return daos class namespace,
      *
      * */
	 public function get_class_namespace(){
	 	 $nameparts = explode("\\", $this::class);
	 	 array_pop($nameparts);
	 	 return implode("\\", $nameparts);
	 }
}
?>