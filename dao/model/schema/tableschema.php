<?php
namespace SaQle\Dao\Model\Schema;

use SaQle\Dao\Field\Interfaces\IValidator;
use SaQle\Dao\Field\Relations\Interfaces\IRelation;
use SaQle\Http\Request\Request;
use SaQle\Services\Container\ContainerService;
use SaQle\Services\Container\Cf;
use SaQle\Dao\Model\Attributes\{CreatorModifierFields, CreateModifyDateTimeFields, SoftDeleteFields};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\Attributes\{PrimaryKey, ForeignKey, NavigationKey, TextFieldValidation, NumberFieldValidation, FileFieldValidation, FileConfig};
use SaQle\Dao\Field\Types\{Pk, TextField, OneToOne, OneToMany, FloatField, IntegerField, BigIntegerField, PhpTimestampField, ManyToMany, FileField, TinyTextField, DateField, TimeField, DateTimeField, TimestampField, BooleanField};
use SaQle\Dao\Field\Exceptions\FieldValidationException;
use SaQle\Security\Models\ModelValidator;
use SaQle\Commons\StringUtils;
use SaQle\Dao\Field\Types\Base\Relation;
use SaQle\Dao\Model\Manager\ModelManager;
use SaQle\Dao\Model\Interfaces\{IModel, IThroughModel, ITableSchema};

abstract class TableSchema implements ITableSchema{
	 use StringUtils;

	 protected static array $instances = [];
	 private array $meta = [];
	 protected Request $request;

	 protected function __construct(...$kwargs){
	 	 $this->initialize();
     }

     final public static function state(){
         $called_class = get_called_class();
         if (!isset($instances[$called_class])){
             $instances[$called_class] = new $called_class();
         }

         return $instances[$called_class];
     }

     private function __clone(){
     }

	 public function set_meta(array $meta){
     	 $this->meta = array_merge($this->meta, $meta);
     }

	 public function meta(){
	 	 return $this->meta;
	 }

	 public function set_request(Request $req){
	 	$this->request = $req;
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
	  * Whether to format php timestamps in cmdt fields to a human readable form.
	  * */
	 public function get_format_cmdt(){
	 	 return $this->meta['format_cmdt'];
	 }

	 /**
	  * Return the type for auto cmdt fields
	  * */
	 public function get_cmdt_type(){
	 	 return $this->meta['db_auto_cmdt_type'];
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
	  * Get db column names.
	  * */
	 public function get_db_column_names(){
	 	return $this->meta['db_columns'];
	 }

	 /**
	  * the names of all the fields
	  * */
	 public function get_field_names(){
	 	return array_keys($this->meta['fields']);
	 }

	 /**
	  * Check if a given field name exists within meta['fields'] array.
	  * */
	 public function does_field_exists(string $name) : bool{
	 	 return array_key_exists($name, $this->meta['fields']);
	 }

	 /**
	  * Return the created at field name
	  * */
	 public function get_created_at_field_name(){
	 	return $this->meta['created_at_field'];
	 }

	 /**
	  * Return the modified at field name
	  * */
	 public function get_modified_at_field_name(){
	 	return $this->meta['modified_at_field'];
	 }

	 /**
	  * Return multitenancy status for model.
	  * */
	 public function is_multitenant(){
	 	return $this->meta['enable_multitenancy'];
	 }

	 /**
	  * Get all non defined fields. These are fields introduced automatically by: auto_cm_fields,
	  * auto_cmdt_fields and soft_delete meta flags
	  * */
     private function get_nondefined_fields(){
     	 $non_defined = [];
     	 if( isset($this->meta['enable_multitenancy']) && $this->meta['enable_multitenancy'] && TENANT_MODEL_CLASS ){
     	 	 $non_defined['tenant'] = new OneToOne(required: false, fdao: TENANT_MODEL_CLASS, field: 'tenant', dname: 'tenant_id');
     	 }

     	 if( isset($this->meta['auto_cm_fields']) && $this->meta['auto_cm_fields'] && AUTH_MODEL_CLASS ){
     	 	 $non_defined['author'] = new OneToOne(required: false, fdao: AUTH_MODEL_CLASS, field: 'author', fk: 'user_id', dname: $this->meta['created_by_field']);
     	 	 $non_defined['modifier'] = new OneToOne(required: false, fdao: AUTH_MODEL_CLASS, field: 'modifier', fk: 'user_id', dname: $this->meta['modified_by_field']);
     	 }

     	 if( isset($this->meta['auto_cmdt_fields']) && $this->meta['auto_cmdt_fields'] ){
     	 	 $non_defined[$this->meta['created_at_field']] = match($this->meta['db_auto_cmdt_type']){
     	 	 	'PHPTIMESTAMP' => new PhpTimestampField(required: false, zero: false, absolute: true),
     	 	 	'DATE'         => new DateField(required: false, strict: false),
     	 	 	'DATETIME'     => new DateTimeField(required: false, strict: false),
     	 	 	'TIME'         => new TimeField(required: false, strict: false),
     	 	 	'TIMESTAMP'    => new TimestampField(required: false, strict: false)
     	 	 };
     	 	 $non_defined[$this->meta['modified_at_field']] = match($this->meta['db_auto_cmdt_type']){
     	 	 	'PHPTIMESTAMP' => new PhpTimestampField(required: false, zero: false, absolute: true),
     	 	 	'DATE'         => new DateField(required: false, strict: false),
     	 	 	'DATETIME'     => new DateTimeField(required: false, strict: false),
     	 	 	'TIME'         => new TimeField(required: false, strict: false),
     	 	 	'TIMESTAMP'    => new TimestampField(required: false, strict: false)
     	 	 };
     	 }

     	 if( isset($this->meta['soft_delete']) && $this->meta['soft_delete'] ){
     	 	 $non_defined[$this->meta['deleted_field']] = new BooleanField(required: false, zero: true, absolute: true);
     	 	 if(AUTH_MODEL_CLASS){
     	 	 	 $non_defined['remover'] = new OneToOne(required: false, fdao: AUTH_MODEL_CLASS, field: 'remover', fk: 'user_id', dname: $this->meta['deleted_by_field']);
     	 	 }
     	 	 $non_defined[$this->meta['deleted_at_field']] = match($this->meta['db_auto_cmdt_type']){
     	 	 	'PHPTIMESTAMP' => new PhpTimestampField(required: false, zero: false, absolute: true),
     	 	 	'DATE'         => new DateField(required: false, strict: false),
     	 	 	'DATETIME'     => new DateTimeField(required: false, strict: false),
     	 	 	'TIME'         => new TimeField(required: false, strict: false),
     	 	 	'TIMESTAMP'    => new TimestampField(required: false, strict: false)
     	 	 };
     	 }

     	 return $non_defined;
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
	 	  * declared on class and field attributes, or the fields themeselves.
	 	  * 
	 	  * All state data will be stored in the meta property in a key => value
	 	  * fashion as explained in meta above.
	 	  * */

         [$db_class, $table_name] = self::get_table_n_dbcontext();
	 	 $this->meta['db_table'] = $table_name;
	 	 $this->meta['db_class'] = $db_class;

	 	 $reflector = new \ReflectionClass($this);

	 	 /**
	 	  * Check CreatorModifierFields attribute on class
	 	  * */
         $cmattributes = $reflector->getAttributes(CreatorModifierFields::class);
         $this->meta['auto_cm_fields'] = $this->meta['auto_cm_fields'] ?? ($cmattributes ? true : MODEL_AUTO_CM_FIELDS);
         $this->meta['created_by_field'] = $this->meta['created_by_field'] ?? MODEL_CREATED_BY_FIELD; #Override by calling set meta
         $this->meta['modified_by_field'] = $this->meta['modified_by_field'] ?? MODEL_MODIFIED_BY_FIELD; #Override by calling set_meta

         /**
          * Check CreateModifyDateTimeFields attribute on class
          * */
         $cmdtattributes = $reflector->getAttributes(CreateModifyDateTimeFields::class);
         $this->meta['auto_cmdt_fields'] = $this->meta['auto_cmdt_fields'] ?? ($cmdtattributes ? true : MODEL_AUTO_CMDT_FIELDS);
         $this->meta['created_at_field'] = $this->meta['created_at_field'] ?? MODEL_CREATED_AT_FIELD; #Override by calling set_meta
         $this->meta['modified_at_field'] = $this->meta['modified_at_field'] ?? MODEL_MODIFIED_AT_FIELD; #Override by calling set_meta
         $this->meta['db_auto_cmdt_type'] = $this->meta['db_auto_cmdt_type'] ?? DB_AUTO_CMDT_TYPE; #Override by calling set_meta
         $this->meta['db_auto_init_timestamp'] = $this->meta['db_auto_init_timestamp'] ?? DB_AUTO_INIT_TIMESTAMP;
         $this->meta['db_auto_update_timestamp'] = $this->meta['db_auto_update_timestamp'] ?? DB_AUTO_UPDATE_TIMESTAMP;
         $this->meta['format_cmdt'] = $this->meta['format_cmdt'] ?? true;

         /**
          * Check SoftDeleteFields attribute on class
          * */
         $delattributes = $reflector->getAttributes(SoftDeleteFields::class);
         $this->meta['soft_delete'] = $this->meta['soft_delete'] ?? ($delattributes ? true : MODEL_SOFT_DELETE);
         $this->meta['deleted_by_field'] = $this->meta['deleted_by_field'] ?? MODEL_DELETED_By_FIELD; #Override by calling set_meta;
         $this->meta['deleted_at_field'] = $this->meta['deleted_at_field'] ?? MODEL_DELETED_AT_FIELD; #Override by calling set_meta;
         $this->meta['deleted_field'] = $this->meta['deleted_field'] ?? MODEL_DELETED_FIELD; #Overrideby calling set_meta;

         $this->meta['fields'] = [];
         $this->meta['nav_field_names'] = [];
         $this->meta['fk_field_names'] = [];
         $this->meta['file_field_names'] = [];
         $this->meta['defined_field_names'] = [];
         $this->meta['db_columns'] = [];

         /**
          * Avoid duplicate data in your table by setting the unique fields key below,
          * it defaults to an empty array to indicate that rows in this table can contain duplicate
          * data.
          * */
         $this->meta['unique_fields'] = $this->meta['unique_fields'] ?? [];

         /**
          * When you have provided more than one unique field,
          * tell the model whether you want to have them be unique together
          * or unique individually. Defaults to false
          * */
         $this->meta['unique_together'] = $this->meta['unique_together'] ?? false;

         /**
          * Tell the model the action to take when an attempt to insert
          * duplicate data is made.
          * 
          * Options include: 
          * IGNORE_DUPLICATE - just ignore the duplicate data and add the rest if there are multiple records to add
          * ABORT_WITH_ERROR - Abort the insert or update operation and throw an exception
          * ABORT_WITHOUT_ERROR - Abort the insert or update operation without throwing an exception.d
          * 
          * Defaults to IGNORE_DUPLICATE
          * 
          * */
         $this->meta['action_on_duplicate'] = $this->meta['action_on_duplicate'] ?? MODEL_ACTION_ON_DUPLICATE;

         /**
          * Enable multitenanncy by adding a tenant field to this model.
          * */
         $this->meta['enable_multitenancy'] = $this->meta['enable_multitenancy'] ?? ENABLE_MULTITENANCY;

         $properties = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
         for($pc = 0; $pc < count($properties); $pc++){
         	 $p = $properties[$pc];
		 	 
		 	 /**
		 	  * Property types may come with a ? if declared nullable, remove it.
		 	  * */
			 $property_type = str_replace("?", "", $p->getType()); 
			 $property_name = $p->getName();

             /**
              * Only take initialized properties.
              * */
			 $rp = new \ReflectionProperty($this::class, $property_name);
			 if( !$rp->isInitialized($this) && $property_type === IField::class ){
			 	 continue;
			 }

			 /**
			  * Modern models have properties of type IField.
			  * */
			 $aliase = null;
			 if($property_type === IField::class){
			 	 $pinstance = $p->getValue($this);
			 }else{
			 	 [$pinstance, $aliase] = $this->convert_property_from_classic_to_modern($p, $property_type);
			 }
			 $aliase = $aliase ?? $property_name;
			 if($pinstance){
			 	 $this->meta['fields'][$aliase] = $pinstance;
			 	 $this->meta['defined_field_names'][] = $aliase;
			 	 if($pinstance instanceof Pk){
			 	 	 $this->meta['pk_name'] = $aliase;
			 	 	 $this->meta['pk_type'] = $pinstance->get_key_type();
			 	 }
			 }
		 }

         /**
          * Acquire non defined fields and combine them with defined fields.
          * */
         $this->meta['fields'] = array_merge($this->meta['fields'], $this->get_nondefined_fields());
		 foreach($this->meta['fields'] as $_k => $mf){
		 	 //set internal field name.
			 $mf->set_property_name($_k);
			 //set the name of the class of field model.
			 $mf->set_model_class($this::class);
		 	 //set the model class pk name
			 $mf->set_model_class_pk($this->meta['pk_name']);
			 //initialize state
			 $mf->initialize();

			 if($mf instanceof FileField){
		 	 	 $this->meta['file_field_names'][] = $_k;
		 	 }elseif($mf instanceof Relation){
		 	 	 if($mf->is_navigation()){
		 	 	 	$this->meta['nav_field_names'][] = $_k;
		 	 	 }else{
		 	 	 	$this->meta['fk_field_names'][] = $_k;
		 	 	 }
		 	 }
			 $this->meta['db_columns'][$_k] = $mf->get_db_column_name();
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
	 	 $primary_type = PRIMARY_KEY_TYPE;
	 	 $aggregate_props = ['reverse' => true];
	 	 $aliase = null;
	 	 /**
	 	  * Check if property has a PrimaryKey attribute
	 	  * */
	 	 $pkattributes = $p->getAttributes(PrimaryKey::class);
	 	 if($pkattributes){
	 	 	$is_primary = true;
	 	 	$primery_type = $pkattributes[0]->getArguments()['type'] ?? $primary_type;
	 	 }
	 	 
	 	 /**
	 	  * Check if property has a ForeignKey attribute
	 	  * */
	 	 $fkattributes = $p->getAttributes(ForeignKey::class);
	 	 if($fkattributes){
	 	 	$fkparams = $this->translate_relation_config($fkattributes[0]->getArguments());
	 	 	if($fkparams === false){
	 	 		return [null, null];
	 	 	}

            $aliase = $fkparams['field'];
	 	 	$is_foreign = true;
	 	 	$is_multiple = ($fkattributes[0]->newInstance())->get_multiple();
	 	 	$aggregate_props = array_merge($aggregate_props, $fkparams, ['dname' => $p->getName()]);
	 	 }

	 	 /**
	 	  * Check if property has a NavigationKey attribute
	 	  * */
	 	 $nkattributes = $p->getAttributes(NavigationKey::class);
	 	 if($nkattributes){
	 	 	$nkparams = $this->translate_relation_config($nkattributes[0]->getArguments());
	 	 	if($nkparams === false){
	 	 		return [null, null];
	 	 	}

            $aliase = $nkparams['field'];
	 	 	$is_navigation = true;
	 	 	$is_multiple = ($nkattributes[0]->newInstance())->get_multiple();
	 	 	$aggregate_props = array_merge($aggregate_props, $nkparams, ['dname' => $p->getName()]);
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
	 	 	return [new Pk($primary_type, ...$aggregate_props), $aliase];
	 	 }

	 	 if($is_foreign || $is_navigation){
	 	 	if($is_navigation){
	 	 		$aggregate_props['isnav'] = true;
	 	 	}
	 	 	if($is_multiple){
	 	 		$aggregate_props['multiple'] = true;
	 	 	}
	 	 	return $is_multiple ? [new OneToMany(...$aggregate_props), $aliase] : [new OneToOne(...$aggregate_props), $aliase];
	 	 }

	 	 if($is_text){
	 	 	return [new TextField(...$aggregate_props), $aliase];
	 	 }

	 	 if($is_number){
	 	 	return $type == "int" ? [new IntegerField(...$aggregate_props), $aliase] : [new FloatField(...$aggregate_props), $aliase];
	 	 }

	 	 if($is_file){
	 	 	return [new FileField(...$aggregate_props), $aliase];
	 	 }

	 	 return null;
	 }

	 /*public static function db(){
	 	 [$db_class, $table_name] = self::get_table_n_dbcontext();
	 	 $manager = Cf::create(ContainerService::class)->createContextModelManager($db_class);
         $manager->initialize($table_name, $db_class);
         return $manager;
	 }*/

	 public function get_defined_field_names(){
	 	return $this->meta['defined_field_names'];
	 }

     public function get_validation_configurations(?array $field_names = null){
     	 $vc = [];
     	 foreach($this->meta['fields'] as $fn => $fv){
     	 	 $column_name = $fv->get_db_column_name();
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
     	 $model_field_names = array_unique(array_merge(array_keys($this->meta['db_columns']), array_values($this->meta['db_columns'])));
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
		 if($operation == 'update' && isset($original_data[$this->meta['pk_name']])){
		 	$validation_feedback['clean'][$this->meta['pk_name']] = $original_data[$this->meta['pk_name']];
		 }
		 return array_merge($validation_feedback['clean'], $file_names_only);
	 }

     private function rename_uploaded_files(&$clean_data, $file_key, $rename_callback, $data_state = null){
	 	$original_clean_data = $clean_data;
	 	if(is_array($clean_data[$file_key]['name'])){
             foreach($clean_data[$file_key]['name'] as $n_index => $n){
                 $clean_data[$file_key]['name'][$n_index] = $this->$rename_callback(
                 	$data_state ? (Object)$data_state : (Object)$original_clean_data, 
                 	$clean_data[$file_key]['name'][$n_index], 
                 	$n_index
                 );
             }
         }else{
             $clean_data[$file_key]['name'] = $this->$rename_callback(
             	$data_state ? (Object)$data_state : (Object)$original_clean_data, 
             	$clean_data[$file_key]['name']
             );
         }
	 }

     public function get_file_configurations(){
     	 $fc = [];
     	 foreach($this->meta['file_field_names'] as $ffn){
     	 	$fc[$ffn] = $this->meta['fields'][$ffn]->get_field_attributes();
     	 }
     	 return $fc;
     }

	 private function prepare_file_data(&$clean_data, $data_state = null){
	 	 /**
	 	  * Prepare files data: Only the file names will be saved in db.
	 	  * 1. Rename the files using the rename callback that was provided on the model if any
	 	  * 2. Get the file path using the path callback that was provided on the model if any.
	 	  * 3. Rest the file values in clean data to file names only
	 	  * */
	 	 $file_data = [];
	 	 foreach($this->meta['file_field_names'] as $ffn){
	 	 	$db_col = $this->meta['db_columns'][$ffn];
	 	 	#if the file exists.
	 	 	if(isset($clean_data[$db_col]) && is_array($clean_data[$db_col])){
	 	 		 $file_config = $this->meta['fields'][$ffn]->get_field_attributes();

	 	 		 //rename files if a rename callback was provided.
	 	 		 if(isset($file_config['rename_callback']) && method_exists($this, $file_config['rename_callback'])){
	 	 		 	 $rename_callback = $file_config['rename_callback'];
	 	 		 	 $this->rename_uploaded_files($clean_data, $db_col, $rename_callback, $data_state);
	 	 		 }

	 	 		 //get the file path
     	 		 $folder_path = $file_config['path'] ?? "";
		         if($folder_path && method_exists($this, $folder_path)){
	 				 $folder_path = $this->$folder_path( $data_state ? (Object)$data_state : (Object)$clean_data);
	 			 }else{
	 			 	 $folder_path = ""; //there should be a default folder path to the media folder
	 			 }
	 			 $file_config['path'] = $folder_path;
	 			 $file_data[$db_col] = (Object)['file' => $clean_data[$db_col], 'config' => $file_config];

	 			 //reset the file value in clean data to file names only.
	     	     $clean_data[$db_col] = is_array($clean_data[$db_col]['name']) ? implode("~", $clean_data[$db_col]['name']) : $clean_data[$db_col]['name'];
	 	 	}
	 	 }
	 	 return $file_data;
	 }

	 private function check_if_duplicate($data, $table_name = null, $model_class = null, $db_class = null){
	 	 if(!$this->meta['unique_fields']){
	 	 	 return false;
	 	 }

	 	 /**
	 	  * Check that the fields provided are defined in model.
	 	  * */
	 	 $all_defined = true;
	 	 $unique_fields = [];

	 	 for($f = 0; $f < count($this->meta['unique_fields']); $f++){
	 	 	 if( !isset($this->meta['fields'][$this->meta['unique_fields'][$f]]) ){
	 	 	 	 $all_defined = false;
	 	 	 	 break;
	 	 	 }

	 	 	 $db_col = $this->meta['db_columns'][$this->meta['unique_fields'][$f]];
	 	 	 if(isset($data[$db_col])){
	 	 	 	 $unique_fields[$db_col] = $data[$db_col];
	 	 	 }
	 	 }

	 	 if(!$all_defined){
	 	 	throw new \Exception("One or more field names provided in meta unique_fields key is not valid!");
	 	 }

	 	 if( count($unique_fields) < count($this->meta['unique_fields']) ){
	 	 	 return false;
	 	 }

         $unique_field_keys = array_keys($unique_fields);
         $first_field = array_shift($unique_field_keys);
         $dao = self::get_associated_model_class();
         if(str_contains($dao, "Throughs")){
         	 $manager = $dao::db2($table_name, $model_class, $db_class)->where($first_field."__eq", $unique_fields[$first_field]);
         }else{
         	 $manager = $dao::db()->where($first_field."__eq", $unique_fields[$first_field]);
         }
	 	 foreach($unique_field_keys as $uf){
	 	 	 $manager = $this->meta['unique_together'] ? $manager->where($uf."__eq", $unique_fields[$uf]) : $manager->or_where($uf."__eq", $unique_fields[$uf]);
	 	 }
	 	 $exists = $manager->limit(records: 1, page: 1)->first_or_default();

	 	 if(!$exists){
	 	 	 return false;
	 	 }

	 	 return [$exists, $unique_fields, $this->meta['unique_together']];
	 }

     private function swap_properties_with_columns($data){
     	 $swapped = [];
     	 foreach($this->meta['fields'] as $pk => $pv){
     	 	 $ck = $this->meta['fields'][$pk]->get_db_column_name();
     	 	 if( array_key_exists($ck, $data) || array_key_exists($pk, $data) ){
     	 	 	 $swapped[$ck] = $data[$ck] ?? $data[$pk];
     	 	 }
     	 }
     	 return $swapped;
     }

	 public function prepare_insert_data($data, $table_name = null, $model_class = null, $db_class = null, $skip_validation = false){
	 	/**
	 	 * Make sure data only uses db column names.
	 	 * */
	 	 $data = $this->swap_properties_with_columns($data);

	 	/**
	 	 * check an attempt to insert duplicate data.
	 	 * */
	 	 $is_duplicate = $this->check_if_duplicate($data, $table_name, $model_class, $db_class);

	 	 /**
	 	  * Inject creator and modifier fields, created and modified date time fields and deleted fields
	 	  * */
	 	 if($this->meta['auto_cm_fields']){
	 	 	$data[$this->meta['created_by_field']] = $this->request->user->user_id ?? 0; #Id of current user
	 	 	$data[$this->meta['modified_by_field']] = $this->request->user->user_id ?? 0; #Id of current user
	 	 }
	 	 if($this->meta['auto_cmdt_fields']){
	 	 	 $data[$this->meta['created_at_field']] = time(); #current date time.
	 	 	 $data[$this->meta['modified_at_field']] = time(); #Current date time
	 	 }
	 	 if($this->meta['soft_delete']){
	 	 	 $data[$this->meta['deleted_field']] = 0; #0 or 1, will be updated according to the operation
	 	 	 $data[$this->meta['deleted_by_field']] = $this->request->user->user_id ?? 0; #Id of current user
	 	 	 $data[$this->meta['deleted_at_field']] = time(); #current date and time stamp
	 	 }

	 	 /**
	 	  * Get validated data.
	 	  * */
	 	 $clean_data = !$skip_validation ? $this->get_clean_data($data, "insert") : $data;

	 	 /**
	 	  * Generate GUID if the primary key is of that type.
	 	  * */
	 	 if($this->meta['pk_type'] === 'GUID'){
	 	 	$clean_data[$this->meta['pk_name']] = $this->guid();
	 	 }

	 	 /**
	 	  * Get file data if any.
	 	  * */
	 	 $file_data = $this->prepare_file_data($clean_data);

	 	 return [$clean_data, $file_data, $is_duplicate, $this->meta['action_on_duplicate']];
	 }

	 public function prepare_update_data($data, $data_state = null, $skip_validation = false){
	 	 /**
	 	 * Make sure data only uses db column names.
	 	 * */
	 	 $data = $this->swap_properties_with_columns($data);

	 	 /**
	 	 * Before anything else happens,
	 	 * check an attempt to insert duplicate data.
	 	 * */
	 	 $is_duplicate = $this->check_if_duplicate($data);

	 	 /**
	 	  * Inject modifier and modified date time fields
	 	  * */
	 	 if($this->meta['auto_cm_fields']){
	 	 	$data[$this->meta['modified_by_field']] = 0; #Id of current user
	 	 }
	 	 if($this->meta['auto_cmdt_fields']){
	 	 	 $data[$this->meta['modified_at_field']] = time(); #Current date time
	 	 }

	 	 /**
	 	  * Get validated data
	 	  * */
	 	 $clean_data = !$skip_validation ? $this->get_clean_data($data, "update") : $data;

	 	 /**
	 	  * For models supporting soft delete, the deleted field(Whatever name its called in meta),
	 	  * must not be updated from here.
	 	  * */
	 	 unset($clean_data[$this->meta['deleted_field']]);

	 	  /**
	 	  * Prepare file data.
	 	  * */
	 	 $file_data = $this->prepare_file_data($clean_data, $data_state);

	 	 /**
	 	  * If there is primary key field, remove it too
	 	  * */
	 	 if(isset($clean_data[$this->meta['pk_name']])){
	 	 	unset($clean_data[$this->meta['pk_name']]);
	 	 }

	 	 return [$clean_data, $file_data, $is_duplicate, $this->meta['action_on_duplicate']];
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
	 			$auto_includes[] = ['relation' => $fv->get_relation(), 'with' => '', 'tuning' => null];
	 		}
	 	}
	 	return $auto_includes;
	 }

	 public function is_include(string $field){
	 	 $includes = array_merge($this->meta['fk_field_names'], $this->meta['nav_field_names']);

	 	 if(!in_array($field, $includes)){
	 		$field = array_flip($this->meta['db_columns'])[$field];
	 		if(!in_array($field, $includes)){
	 			 return false;
	 		}
	 	 }

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

	 /**
	  * Get the raw values of fields
	  * @param bool $with_db_names: Whether the returned values should have db column names
	  * or regular model property names.
	  * @return array
	  * */
	 public function get_raw_values(bool $with_db_names = false, bool $partial = false) : array{
	 	 $values = [];
	 	 if($with_db_names){
	 	 	 foreach($this->meta['fields'] as $fn => $fv){
	 	 	 	 $col_name = $fv->get_db_column_name();
	 	 	 	 $values[$col_name] = $fv->get_value();
	 	     }
	 	 }else{
	 	 	 foreach($this->meta['fields'] as $fn => $fv){
	 	 	     $values[$fn] = $fv->get_value();
	 	     }
	 	 }

	 	 if($partial){
	 	 	 $filtered = [];
	 	 	 foreach($values as $k => $v){
	 	 	 	 if($v){
	 	 	 	 	 $filtered[] = $v;
	 	 	 	 }
	 	 	 }
	 	 	 return $filtered;
	 	 }
	 	 
	 	 return $values;
	 }

	 static public function find_property_by_attribute(string $class_name, string $attribute_class_name) : ?string{
         $ref = new \ReflectionClass($class_name);
         $properties = $ref->getProperties();
	     foreach($properties as $property){
	         $attributes = $property->getAttributes($attribute_class_name);
	        
	         if (!empty($attributes)) {
	             return $property->getName();
	         }
	     }
    
         return null;
     }

     static public function find_property_by_type(string $class_name, string $type_name) : ?string{
         $ref = new \ReflectionClass($class_name);
         $properties = $ref->getProperties();
	     foreach($properties as $property){
	     	 $property_type = str_replace("?", "", $property->getType()); 
	     	 if($property_type && $property_type === $type_name){
	     	 	 return $property->getName();
	     	 }
	     }
    
         return null;
     }

     public function find_property(string $property_name) : array{
         $ref = new \ReflectionClass($this::class);
         $properties = $ref->getProperties();
	     foreach($properties as $property){
	     	 $name = $property->getName();
	     	 if($name == $property_name){
	     	 	 $property_type = str_replace("?", "", $property->getType()); 
	     	 	 return [$name, $property_type];
	     	 }
	     }
    
         return [null, null];
     }

     public function find_properties() : array{
     	 $props = [];
         $ref = new \ReflectionClass($this);
         $properties = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
	     foreach($properties as $property){
	     	 $name = $property->getName();
	     	 $property_type = str_replace("?", "", $property->getType());
	     	 $props[$name] = $property_type;
	     }
    
         return $props;
     }

     public function __toString(){
     	 $raw = $this->get_raw_values(with_db_names: false);
		 return json_encode($raw);
	 }

	 public static function get_associated_model_class(){
	 	 $called_class = get_called_class();
	 	 $parts = explode("\\", $called_class);
	 	 $schema_name = array_pop($parts);
	 	 $model_name = str_replace("Schema", "", $schema_name);
	 	 if(str_contains($called_class, "Throughs")){
	 	 	 $index = array_search("Schema", $parts);
	 	 	 unset($parts[$index]);
	 	 }else{
	 	     array_pop($parts);
	 	 }
	 	 $parts[] = $model_name;
	 	 return implode("\\", $parts);
	 }
}
?>