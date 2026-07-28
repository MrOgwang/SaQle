<?php

namespace SaQle\Core\Support;

/**
 * 
 * The Field class converts a contract property to a model field:
 * 
 * */

class Field {

	 private string $name;

     private string $id;

     private string $type;

     private bool $required;

     private mixed $default = null;

     private bool $nullable = false;

     private bool $readonly = false;

     private bool $promoted = false;

	 public function initialize(
	 	 string $name,
         string $id,
         string $type,
         bool   $required,
         mixed  $default = null,
         bool   $nullable = false,
         bool   $readonly = false,
         bool   $promoted = false
     ){
     	 $this->name     = $name;
     	 $this->id       = $id;
     	 $this->type     = $type;
     	 $this->required = $required;
     	 $this->default  = $default;
     	 $this->nullable = $nullable;
     	 $this->readonly = $readonly;
     	 $this->promoted = $promoted;
	 }

	 public function get_name() : string {
	 	 return $this->name;
	 }

	 public function get_form_field_attrs() : array {
	 	 return [
	 	 	 'name'     => $this->name,
	 	 	 'id'       => $this->id,
     	     'type'     => $this->type,
     	     'required' => $this->required,
     	     'default'  => $this->default,
     	     'nullable' => $this->nullable,
     	     'readonly' => $this->readonly,
     	     'promoted' => $this->promoted
	 	 ];
	 }
}
