<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use Closure;
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\Base\RelationField;
use SaQle\Orm\Entities\Field\Types\VirtualField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;
use ReflectionClass;

class Field implements IField {

	 //the logical field name on the model
	 protected string $name;

	 //the database column name - defaults to name
	 #[FieldDefinition()]
	 protected ?string $column = null;

	 //the actual, unmodified value of field
	 protected mixed $value = null;

	 //the primitive type of the field
	 #[FieldDefinition()]
	 protected ColumnType $type;

	 //the default value or callable
	 #[FieldDefinition()]
	 protected mixed $default = null;

	 //whether field is required
	 #[FieldDefinition()]
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

	 /*public function get_definition(){
	 	 $is_field = $this instanceof VirtualField || ($this instanceof RelationField && $this->navigation) ? false : true;
	     if (!$is_field) {
	         return null;
	     }

         $def = new FieldDefinition();
         $def->name = $this->column;
         $def->type = $this->type;
         $def->unique = $this->unique;
         $def->index = $this->index;
         $def->length = $this->length ?? null;
         $def->nullable = !$this->required;
         $def->primary = $this->primary;
         $def->auto_increment = $this->auto ?? false;
         $def->auto_init_timestamp = $this->auto_now_add ?? false;
         $def->auto_update_timestamp = $this->auto_now ?? false;
         $def->default = $this->default;

         return $def;
     }*/

	 function get_definition(string $attribute_class): ?object {
	 	 $is_field = $this instanceof VirtualField || ($this instanceof RelationField && $this->navigation) ? false : true;
	     if (!$is_field) {
	         return null;
	     }

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

	     return (object)$result;
	 }

     public function is_state_valid(){
     	 return true;
     }

}

