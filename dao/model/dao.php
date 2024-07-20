<?php
namespace SaQle\Dao\Model;

use SaQle\Dao\Field\Interfaces\IValidator;
use SaQle\Dao\Field\Relations\Interfaces\IRelation;
use SaQle\Http\Request\Request;
use SaQle\Services\Container\ContainerService;
use SaQle\Services\Container\Cf;
use SaQle\Dao\Model\Attributes\{CreatorModifierFields, CreateModifyDateTimeFields, SoftDeleteFields};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\Attributes\{PrimaryKey, ForeignKey, NavigationKey, TextFieldValidation, NumberFieldValidation, FileFieldValidation,FileField};
use SaQle\Dao\Field\Types\{Pk, Text, OneToOne, OneToMany, Number, ManyToMany, File};
use SaQle\Dao\Field\Exceptions\FieldValidationException;
use SaQle\Security\Models\ModelValidator;
use SaQle\Commons\StringUtils;

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
     	$this->meta = array_merge($meta, $this->meta);
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

	 private function initialize(){
	 	 /**
	 	  * Inspect the class and initialize state based on the configurations
	 	  * declared on class and field attributes, or the fields themeselevs.
	 	  * 
	 	  * All state data will be stored in the meta property in a key => value
	 	  * fashion as explained in meta above.$p->getAttributes(NavigationKey::class)
	 	  * */

         /**
          * Set the default database table name associated with model.
          * */
	 	 $nameparts = explode("\\", $this::class);
	 	 $lastpart  = end($nameparts);
	 	 $this->meta['db_table'] = strtolower($lastpart)."s";

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
         $this->meta['modified_at_field'] = 'date_modified'; #Override by calling set_meta

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
		 foreach($properties as $p){
		 	 /**
		 	  * Property types come with a ? if declared nullable, remove it.
		 	  * */
			 $property_type = str_replace("?", "", $p->getType()); 
			 $property_name = $p->getName();

			 /**
			  * Modern models have properties of type IField.
			  * */
			 if($property_type === IField::class){
			 	 $pinstance = $p->getValue($this);
			 	 if($pinstance instanceof Pk){
			 	 	$this->meta['pk_name'] = $property_name;
			 	 	$this->meta['pk_type'] = $pinstance->get_key_type();
			 	 }elseif($pinstance instanceof OneToOne || $pinstance instanceof OneToMany || $pinstance instanceof ManyToMany){
			 	 	 if($pinstance->is_navigation()){
			 	 	 	$this->meta['nav_field_names'][] = $property_name;
			 	 	 }else{
			 	 	 	$this->meta['fk_field_names'][] = $property_name;
			 	 	 }
			 	 }elseif($pinstance instanceof File){
			 	 	$this->meta['file_field_names'][] = $property_name;
			 	 }
			 	 $this->meta['fields'][$property_name] = $pinstance;
			 }
			 /**
			  * Classic models have simple property types such as string, float, int
			  * */
			 else{
			 	 $this->meta['fields'][$property_name] = $this->convert_property_from_classic_to_modern($p, $property_name);
			 }
		 }
	 }

	 private function convert_property_from_classic_to_modern($p){
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
	 	 	$is_foreign = true;
	 	 	$is_multiple = ($fkattributes[0]->newInstance())->get_multiple();
	 	 	$aggregate_props = array_merge($aggregate_props, $fkattributes[0]->getArguments());
	 	 }

	 	 /**
	 	  * Check if property has a NavigationKey attribute
	 	  * */
	 	 $nkattributes = $p->getAttributes(NavigationKey::class);
	 	 if($nkattributes){
	 	 	$is_navigation = true;
	 	 	$is_multiple = ($nkattributes[0]->newInstance())->get_multiple();
	 	 	$aggregate_props = array_merge($aggregate_props, $nkattributes[0]->getArguments());
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
	 	      * Check if property has FileField attribute.
	 	     * */
	 	 	$cfattributes = $p->getAttributes(FileField::class);
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
	 	 		$this->meta['nav_field_names'][] = $p_name;
	 	 	}
	 	 	if($is_multiple){
	 	 		$aggregate_props['multiple'] = true;
	 	 		$this->meta['fk_field_names'][] = $p_name;
	 	 	}
	 	 	return $is_multiple ? new OneToMany(...$aggregate_props) : new OneToOne(...$aggregate_props);
	 	 }

	 	 if($is_text){
	 	 	return new Text(...$aggregate_props);
	 	 }

	 	 if($is_number){
	 	 	return new Number(...$aggregate_props);
	 	 }

	 	 if($is_file){
	 	 	$this->meta['file_field_names'][] = $p_name;
	 	 	return new File(...$aggregate_props);
	 	 }
	 }

	 public static function db(){
	 	 $db_class = false;
	 	 $table_name = null;
	 	 $context_classes = array_keys(DB_CONTEXT_CLASSES);
	 	 $current_model_name = get_called_class();
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

	 	 if(!$db_class || !$table_name){
	 	 	throw new \Exception($current_model_name.": Model not registered with any db contexts!");
	 	 }

	 	 $dbcontext = Cf::create(ContainerService::class)->createDbContext($db_class);
         return $dbcontext->get($table_name);
	 }

     public function get_validation_configurations(){
     	 $vc = [];
     	 foreach($this->meta['fields'] as $fn => $fv){
     	 	 $fvc = $fv->get_validation_configurations();
     	 	 if($fvc){
     	 	 	 $vc[$fn] = $fvc;
     	 	 }
     	 }
     	 return $vc;
     }

	 public function get_clean_data(array $data){
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
	 	 $vc = $this->get_validation_configurations();
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
			 throw new FieldValidationException($validation_feedback['dirty']);
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

	 public function prepare_insert_data($data){
	 	 $clean_data = $this->get_clean_data($data);

	 	 /**
	 	  * Generate GUID if the primary key is of that type.
	 	  * */
	 	 if($this->meta['pk_type']){
	 	 	$clean_data[$this->meta['pk_name']] = $this->guid();
	 	 }

	 	 /**
	 	  * Inject creator and modifier fields, created and modified date time fields and deleted
	 	  * fields
	 	  * */
	 	 if($this->meta['auto_cm_fields']){
	 	 	$clean_data[$this->meta['created_by_field']] = 0; #Id of current user
	 	 	$clean_data[$this->meta['modified_by_field']] = 0; #Id of current user
	 	 }
	 	 if($this->meta['auto_cmdt_fields']){
	 	 	 $clean_data[$this->meta['created_at_field']] = 0; #current date time.
	 	 	 $clean_data[$this->meta['modified_at_field']] = 0; #Current date time
	 	 }
	 	 if($this->meta['soft_delete']){
	 	 	$clean_data[$this->meta['deleted_field']] = 0; #0 or 1, will be updated according to the operation
	 	 	$clean_data[$this->meta['deleted_by_field']] = 0; #Id of current user
	 	 	$clean_data[$this->meta['deleted_at_field']] = 0; #current date and time stamp
	 	 }

	 	 /**
	 	  * Prepare file data.
	 	  * */
	 	 $file_data = $this->prepare_file_data($clean_data);

	 	 return [$clean_data, $file_data];
	 }

	 public function prepare_update_data($data){
	 	 $clean_data = $this->get_clean_data($data);

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
}
?>