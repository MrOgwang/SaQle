<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use Closure;
use SaQle\Orm\Entities\Field\Interfaces\IField;

class Field implements IField {

	 //the logical field name on the model
	 protected string $name;

	 //the database column name - defaults to name
	 protected ?string $column = null;

	 //the database column type
	 protected string $column_type;

	 //the actual, unmodified value of field
	 protected mixed $value = null;

	 //the primitive type of the field
	 protected string $type;

	 //the default value or callable
	 protected mixed $default = null;

	 //whether field is required
	 protected bool $required = false;

	 //whether to allow null values
	 protected bool $nullable = true;

	 //whether to persist to db or not
	 protected bool $virtual = false;

	 //whether this is a unique field
	 protected bool $unique = false;

	 //whether to index this field
	 protected bool $index = false;

	 //whether this is a primary key field
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

	 public function type(string $type){
	 	 $this->type = $type;
	 	 return $this;
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
}

