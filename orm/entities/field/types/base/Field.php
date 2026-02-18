<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use Closure;
use SaQle\Core\Support\FieldValidator;
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\Base\RelationField;
use SaQle\Orm\Entities\Field\Types\VirtualField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};
use ReflectionClass;

class Field implements IField {

	 /**
	  * Will keep track of field state errors. We are not making any assumptions
	  * concerning the developer's intentions, so we will just complain loudly
	  * and let the developer handle it
	  * */
	 protected array $errors = [];

	 //the parent model class
	 protected ?string $model_class = null;

	 //the pk name on parent model class
	 protected ?string $model_pk = null;

	 //the logical field name on the model
	 protected ?string $name = null;

	 //the database column name - defaults to name
	 #[FieldDefinition()]
	 protected ?string $column = null;

	 //the actual, unmodified value of field
	 protected mixed $value = null;

	 //the database column type of the field
	 #[FieldDefinition()]
	 protected ColumnType $type;

	 //the primitive type of the field
	 #[ShouldValidate()]
	 protected ?string $native_type = null;

	 //the default value or callable
	 #[FieldDefinition()]
	 protected mixed $default = null;

	 //whether field is required
	 #[FieldDefinition()]
	 #[ShouldValidate()]
	 protected bool $required = false;

	 //whether to allow null values
	 #[FieldDefinition()]
	 protected bool $nullable = true;

	 //whether to persist to db or not
	 protected bool $virtual = false;

	 //whether this is a unique field
	 #[FieldDefinition()]
	 protected bool $unique = false;

	 //whether to index this field
	 #[FieldDefinition()]
	 protected bool $index = false;

	 //whether this is a primary key field
	 #[FieldDefinition()]
	 protected bool $primary = false;

	 //the render callback is used to change how the value is presented
	 protected ?Closure $render_callback = null;

	 //initialize a new field
	 public function __construct(...$kwargs){
	 	 foreach($kwargs as $k => $v){
	 	 	 $this->$k = $v;
	 	 }
	 }

	 public function name(string $name){
	 	 $this->name = $name;
	 	 return $this;
	 }

	 public function get_name(){
	 	 return $this->name;
	 }

	 public function column(string $column){
	 	 $this->column = $column;
	 	 return $this;
	 }

	 public function get_column(){
	 	 return $this->column;
	 }

	 public function value(mixed $value){
	 	 $this->value = $value;
	 	 return $this;
	 }

	 public function get_value(){
	 	 return $this->value;
	 }

	 public function get_type(){
	 	 return $this->type;
	 }

	 public function default(mixed $default){
	 	 $this->default = $default;
	 	 return $this;
	 }

	 public function get_default(){
	 	 return $this->default;
	 }

	 public function required(bool $required = true){
	 	 $this->required = $required;
	 	 return $this;
	 }

	 public function is_required(){
	 	 return $this->required;
	 }

	 public function nullable(bool $nullable = true){
	 	 $this->nullable = $nullable;
	 	 return $this;
	 }

	 public function is_nullable(){
	 	 return $this->nullable;
	 }

	 public function virtual(bool $virtual = true){
	 	 $this->virtual = $virtual;
	 	 return $this;
	 }

	 public function is_virtual(){
	 	 return $this->virtual;
	 }

	 public function unique(bool $unique = true){
	 	 $this->unique = $unique;
	 	 return $this;
	 }

	 public function is_unique(){
	 	 return $this->unique;
	 }

	 public function index(bool $index = true){
	 	 $this->index = $index;
	 	 return $this;
	 }

	 public function is_index(){
	 	 return $this->index;
	 }

	 public function primary(bool $primary = true){
	 	 $this->primary = $primary;
	 	 return $this;
	 }

	 public function is_primary(){
	 	 return $this->primary;
	 }

	 public function render_callback(callable $callback){
	 	 $this->render_callback = $callback;
	 	 return $this;
	 }

	 public function get_render_callback(){
	 	 return $this->render_callback;
	 }

	 protected function get_properties_with_attribute(string $attribute_class){
	 	 $reflection = new ReflectionClass($this);
	     $result = [];

	     foreach($reflection->getProperties() as $property){
	         $attributes = $property->getAttributes($attribute_class);

	         if(empty($attributes)){
	             continue;
	         }

	         $property->setAccessible(true);

	         $attribute_instance = $attributes[0]->newInstance();

	         $key = $attribute_instance->key ?? $property->getName();
	         $value = $property->getValue($this);

	         $result[$key] = $value;
	     }

	     return $result;
	 }

	 public function get_definition(): ?object {
	 	 $is_field = $this instanceof VirtualField || ($this instanceof RelationField && $this->navigation) ? false : true;
	     if (!$is_field) {
	         return null;
	     }

	     return (object)$this->get_properties_with_attribute(FieldDefinition::class);
	 }

	 protected function validate_field_state(){
	 	 if(!$this->name){
	 	 	 $this->errors[] = "A field name is required!";
	 	 }

	 	 if($this->required === true && $this->nullable === true){
     	 	 $this->errors[] = "A required field cannot be nullable!";
     	 }
	 }

     public function is_state_valid(){
     	 return empty($this->errors) ? true : false;
     }

     protected function initialize_defaults(){
     	 //if column name isnt provided, set it to the field name.
     	 if(!$this->column){
     	 	 $this->column = $this->name;
     	 }
     }

     protected function get_validation_rules() : array {
     	 $is_field = $this instanceof VirtualField || ($this instanceof RelationField && $this->navigation) ? false : true;
	     if (!$is_field){
	         return [];
	     }

	     return $this->get_properties_with_attribute(ShouldValidate::class);
     }

     public function validator(){
     	 return new FieldValidator($this->get_validation_rules());
     }

     public function build(string $name, string $model_class, string $model_pk){

     	 $this->name = $name;
     	 $this->model_class = $model_class;
     	 $this->model_pk = $model_pk;
     	 $this->validate_field_state();
	 	 $this->initialize_defaults();
	 	 
     }
}

