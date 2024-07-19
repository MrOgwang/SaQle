<?php
namespace SaQle\Dao\Model;

use SaQle\Dao\Field\FieldCollection;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\Field;
use SaQle\Dao\Field\Attributes\{ForeignKey, NavigationKey};
use SaQle\Dao\Model\Attributes\{CreateModifyDateTimeFields, CreatorModifierFields, SoftDeleteFields};
use ReflectionProperty;

class Model implements IModel{
	 private IField $fields;
	 private array  $attributes;
	 public function __construct(private IModel $dao){
	 	 $this->fields = new FieldCollection();
		 $reflector = new \ReflectionClass($dao);
         $this->attributes = $this->set_attributes($reflector->getAttributes());
         $properties = $reflector->getProperties(ReflectionProperty::IS_PUBLIC);
		 foreach($properties as $p){
		 	 //property types come with a ? if it was declared nullable, so remove it.
			 $property_type       = str_replace("?", "", $p->getType()); 
			 if($property_type === "SaQle\Dao\Field\Interfaces\IField"){
			 	$pinstance = $p->getValue($dao);
			 	$property_attributes = $pinstance->get_attributes();
			 	$property_type = $pinstance->get_data_type();
			 }else{
			 	$property_attributes = $this->set_attributes($p->getAttributes());
			 }
			 $property_name = $p->getName();
		 	 $property_default_value = $p->getDefaultValue();
			 $field = new Field();
			 $field->set_name($property_name);
			 $field->set_type((string)$property_type);
			 $field->set_default($property_default_value);
			 $field->set_value($property_default_value);
			 //add field attributes.
			 $field->set_attributes($property_attributes);
			 $field->set_raw_attributes(array_merge($p->getAttributes(NavigationKey::class), $p->getAttributes(ForeignKey::class)));
			 $this->fields->add($field);
		 }
	 }
	 public function get_dao(){
	 	return $this->dao;
	 }
	 public function get_fields(){
	 	return $this->fields->get_fields();
	 }
	 public function get_field_names(){
	 	return $this->fields->get_field_names();
	 }
	 private function set_attributes($attributes){
         $result = [];
	     foreach ($attributes as $attribute){
	         $result[$attribute->getName()] = $attribute->getArguments();
	     }
	     return $result;
     }

     public function get_primary_key_field() : Field | null{
     	 return $this->fields->get_primary_key_field();
     }
     public function get_navigation_fields() : array{
     	 return $this->fields->get_navigation_fields();
     }
     public function get_navigation_field_names() : array{
     	 return $this->fields->get_navigation_field_names();
     }
     public function get_foreign_keys() : array{
     	return $this->fields->get_foreign_keys();
     }
     public function get_foreign_key_names() : array{
     	return $this->fields->get_foreign_key_names();
     }
     public function get_primary_key_name() : string | null{
     	 return $this->fields->get_primary_key_name();
     }
     public function get_validation_configurations(?array $fields = null) : array{
     	 return $this->fields->get_validation_configurations($fields);
     }
     public function get_file_configurations() : array{
     	 return $this->fields->get_file_configurations();
     }
     public static function guid(){
         if(function_exists('com_create_guid') === true){
             return trim(com_create_guid(), '{}');
         }
         return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
     }
     public function get_include_field(string $field){
     	return $this->fields->get_include_field($field);
     }

     public function has_creatormodifierfields(){
     	 $has = array_key_exists(CreatorModifierFields::class, $this->attributes);
     	 if(!$has){
     	 	$has = $this->dao->get_auto_cm();
     	 }
     	 return $has;
     }
     public function has_createmodifydatetimefields(){
     	 $has = array_key_exists(CreateModifyDateTimeFields::class, $this->attributes);
     	 if(!$has){
     	 	 $has = $this->dao->get_auto_cmdt();
     	 }
     	 return $has;
     }
     public function has_softdeletefields(){
     	 $has = array_key_exists(SoftDeleteFields::class, $this->attributes);
     	 if(!$has){
     	 	$has = $this->dao->get_soft_delete();
     	 }
     	 return ;
     }
}
?>