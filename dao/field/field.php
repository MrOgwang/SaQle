<?php
 declare(strict_types = 1);
 namespace SaQle\Dao\Field;

 use SaQle\Dao\Field\Interfaces\IField;
 
 class Field implements IField{
 	 /**
 	 * Create a new field instance
 	 * @param string $name:       the name of the field
 	 * @param string $type        the field type
 	 * @param nullable $default:  the default value of this field
 	 * @param nullable $value:    the provided value of this field
 	 * @param nullable array $vc: an array of validation confiugrations for this field
 	 * @param array $attributes:  an array of field attributes
 	 */
 	 private array $raw_attributes = [];
	 function __construct(
	 	private ?string $name      = null,
	 	private ?string $type      = null,
	 	private         $default    = null,
	 	private         $value      = null,
	 	private ?array  $vc         = null,
	 	private array   $attributes = []
	 ){}

	 /*setters*/
	 public function set_raw_attributes($raw_attributes){
	 	$this->raw_attributes = $raw_attributes;
	 }
	 public function set_name(string $name){
		 $this->name = $name;
	 }
	 public function set_type(string $type){
		 $this->type = $type;
	 }
	 public function set_default($default){
		 $this->default = $default;
	 }
	 public function set_value($value){
		 $this->value = $value;
	 }
	 public function set_vc(array $vc){
		 $this->vc = $vc;
	 }
	 public function set_attributes(array $attributes){
	 	$this->attributes = $attributes;
	 }

	 /*getters*/
	 public function get_raw_attributes(){
	 	return $this->raw_attributes;
	 }
	 public function get_name(){
		 return $this->name;
	 }
	 public function get_type(){
		 return $this->type;
	 }
	 public function get_default(){
		 return $this->default;
	 }
	 public function get_value(){
		 return $this->value;
	 }
	 public function get_vc(){
		 return $this->vc;
	 }
	 public function get_attributes() : array{
	 	 return $this->attributes;
	 }

	 public function is_primary_key() : bool{
         return array_key_exists("SaQle\Dao\Field\Attributes\PrimaryKey", $this->attributes);
     }

     public function is_navigation_field() : bool{
         return array_key_exists("SaQle\Dao\Field\Attributes\NavigationKey", $this->attributes);
     }

     public function is_foreign_key() : bool{
         return array_key_exists("SaQle\Dao\Field\Attributes\ForeignKey", $this->attributes);
     }

     public function get_primary_key_type() : string | null{
     	if($this->is_primary_key()){
     		 return $this->attributes['SaQle\Dao\Field\Attributes\PrimaryKey']['type'];
     	}
     	return null;
     }

     public function is_numeric(){
     	 return array_key_exists("SaQle\Dao\Field\Attributes\NumberFieldValidation", $this->attributes);
     }

     public function get_validation_attributes(){
     	 $attributes = $this->attributes['SaQle\Dao\Field\Attributes\NumberFieldValidation']
     	 ?? ( $this->attributes['SaQle\Dao\Field\Attributes\TextFieldValidation'] ?? 
     	 	($this->attributes['SaQle\Dao\Field\Attributes\FileFieldValidation'] ?? []));
     	 return $attributes;
     }
 }
?>