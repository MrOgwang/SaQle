<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

class Field {

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

	 public function name(string $name){
	 	 $this->name = $name;
	 	 return $this;
	 }

	 public function column(string $column){
	 	 $this->column = $column;
	 	 return $this;
	 }

	 public function value(mixed $value){
	 	 $this->value = $value;
	 	 return $this;
	 }

	 public function type(string $type){
	 	 $this->type = $type;
	 	 return $this;
	 }

	 public function default(mixed $default){
	 	 $this->default = $default;
	 	 return $this;
	 }

	 public function required(bool $required = true){
	 	 $this->required = $required;
	 	 return $this;
	 }

	 public function nullable(bool $nullable = true){
	 	 $this->nullable = $nullable;
	 	 return $this;
	 }

	 public function virtual(bool $virtual = true){
	 	 $this->virtual = $virtual;
	 	 return $this;
	 }

	 public function unique(bool $unique = true){
	 	 $this->unique = $unique;
	 	 return $this;
	 }

	 public function index(bool $index = true){
	 	 $this->index = $index;
	 	 return $this;
	 }

	 public function primary(bool $primary = true){
	 	 $this->primary = $primary;
	 	 return $this;
	 }
}

