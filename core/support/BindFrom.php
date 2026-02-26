<?php
namespace SaQle\Core\Support;

/**
 * The BindFrom attribute is used on controller method parameters.
 * 
 * During controller method execution, parameters are automatically injected into the method.
 * 
 * The BindFrom attribute tells the Runtime where to extract the parameter from. Options include:
 * session - extract parameter from session
 * cookie - extract parameter from cookie
 * header - extract parameter from header
 * input - extract paramater from input data
 * path - extract parameter from route path
 * query - extract parameter from route queries
 * db - fetch parameter from the database
 * */

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class BindFrom {
	 
	 /**
	  * Where the data will be extracted from by the parameter resolver
	  * 
	  * session - extract parameter from session
      * cookie - extract parameter from cookie
      * header - extract parameter from header
      * input - extract paramater from input data
      * path - extract parameter from route path
      * query - extract parameter from route queries
      * db - fetch parameter from the database
      * di, container - the DI container
	  * */
	 public string $from {
	 	 set(string $value){
	 	 	 $this->from = $value;
	 	 }

	 	 get => $this->from;
	 }

     /**
      * The key is the identifier of the data item
      * 
      * It will be derived from the parameter name as defined in the controller method
      * definition if not provided
      * */
	 public string $key {
	 	 set(string $value){
	 	 	 $this->key = $value;
	 	 }

	 	 get => $this->key;
	 }

     /**
      * Embedded tells us whether the key points to a completely formed object or
      * the object will have to be scrambled from various input params
      * */
	 public ?bool $embedded = null {
	 	 set(?bool $value){
	 	 	 $this->embedded = $value;
	 	 }

	 	 get => $this->embedded;
	 }

	 /**
	  * The validation rules to be used for valiadtion
	  * */
	 public array $rules = [] {
	 	 set(array $value){
	 	 	 $this->rules = $value;
	 	 }

	 	 get => $this->rules;
	 }

	 public function __construct(string $source, string $key = '', array $rules = [], ?bool $embedded = null){
	 	 $this->from     = $source;
	 	 $this->key      = $key;
	 	 $this->embedded = $embedded;
	 	 $this->rules = $rules;
	 }

	 public function set_key(string $key, bool $update = false){
	 	 if($update){
	 	 	 $this->key = $key;
	 	 	 return;
	 	 }

	 	 if(!$this->key){
             $this->key = $key;
         }
	 }
}
