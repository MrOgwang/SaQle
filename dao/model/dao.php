<?php
namespace SaQle\Dao\Model;

use SaQle\Dao\Field\Interfaces\IValidator;
use SaQle\Dao\Field\Relations\Interfaces\IRelation;
use SaQle\Http\Request\Request;
use SaQle\Services\Container\ContainerService;
use SaQle\Services\Container\Cf;

abstract class Dao implements IModel{
	 protected Request $request;

	 /**
	 	 * This is key => val array of data access object meta data with the following optional keys
	 	 * @var string name_property : the property from which a value object deroves its name
	 	 * 
	 	 * @var array validators     : a key => val array of validators to apply to each of the properties defined in dao, 
	 	 *                             where the key represents the property name and the val is the validator instance.
	 	 * 
	 	 * @var array relations      : a key => val array of relationships to impose on each property defined in dao,
	 	 *                             where a key reperesnts the property name and the val is the relationship instance.
	 	 * 
	 	 * @var array types          : a key => val array of types for one or more properties defined in the dao,
	 	 *                             where a key represents the property name and the val is the type class. Will be used mostly with
	 	 *                             file types.
	 	 * */
	 private   array   $meta = [];

	 public function __construct(...$field_values){
	 	 $this->set_field_values(...$field_values);
	 }

	 public function __clone(){}

     /**
      * If this dao is instantiated with field values passed in the constructor, 
      * assign the values to the relevant properties.
      * @param array $values : key => val array of property names and their values
      * */
	 public function set_field_values(...$field_values){
	 	 if(count($field_values) > 0){
	 	 	$reflection_class = new \ReflectionClass(get_class($this));
		 	 foreach($field_values as $prop => $val){
		 	 	 $reflection_class->getProperty($prop)->setValue($this, $val);
		 	 }
	 	 }
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
     	$this->meta = $meta;
     }


     

     /**
      * Set the relations property of the meta array
      * @param array relations: 
      * @param bool  $merge : Whether to merge with current relations or overwrite
      * */
	 public function set_meta_relations(array $relations, bool $merge = false){
	 	 $this->meta['relations'] = !$merge || !isset($this->meta['relations']) ? $relations : array_merge($this->meta['relations'], $relations);
	 }

	 /**
      * Set the relation instance for a property
      * @param string   $property_name : the name of the property for which to set relation instance
      * @param Relation $relation      : the relation instance to set
      * @param bool     $overwrite     : Whether to overwrite current property relation or not. Defaults to true.
      * */
	 public function set_property_relation(string $property_name, IRelation $relation, bool $overwrite = true){
	 	 if($overwrite){
	 	 	 $this->meta['relations'][$property_name] = $relation;
	 	 }
	 }

	 /**
      * Set the validators property of the meta array
      * @param array validators: 
      * @param bool  $merge : Whether to merge with current validators or overwrite
      * */
	 public function set_meta_validators(array $validators, bool $merge = false){
	 	 $this->meta['validators'] = !$merge || !isset($this->meta['validators']) ? $validators : array_merge($this->meta['validators'], $validators);
	 }

	 /**
      * Set the validator instance for a property
      * @param string    $property_name : the name of the property for which to set relation instance
      * @param Validator $validator     : the validator instance to set
      * @param bool      $overwrite     : Whether to overwrite current property validator or not. Defaults to true.
      * */
	 public function set_property_validator(string $property_name, IValidator $validator, bool $overwrite = true){
	 	 if($overwrite){
	 	 	 $this->meta['validators'][$property_name] = $validator;
	 	 }
	 }

	 /**
      * Set the types property of the meta array
      * @param array types: 
      * @param bool  $merge : Whether to merge with current types or overwrite
      * */
	 public function set_meta_types(array $types, bool $merge = false){
	 	 $this->meta['types'] = !$merge || !isset($this->meta['types']) ? $types : array_merge($this->meta['types'], $types);
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

     /**
      * Return the dao name property as set in the meta data
      * */
	 public function get_name_property(){
	 	 $meta = $this->meta();
	 	 return array_key_exists("name_property", $meta) ? $meta['name_property'] : "";
	 }

     /**
      * Return a property relation as set in the meta data
      * */
	 public function get_property_relation(string $property_name){
	 	 $meta = $this->meta();
	 	 return $meta['relations'][$property_name] ?? null;
	 }

	 /**
      * Return a property validator as set in the meta data
      * */
	 public function get_property_validator(string $property_name){
	 	 $meta = $this->meta();
	 	 return $meta['validators'][$property_name] ?? null;
	 }

	 /**
      * Return a property type as set in the meta data
      * */
	 public function get_property_type(string $property_name){
	 	 $meta = $this->meta();
	 	 return $meta['types'][$property_name] ?? null;
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
}
?>