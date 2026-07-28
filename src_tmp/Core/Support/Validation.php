<?php
namespace SaQle\Core\Support;

/**
 * The Validation attribute is used on controller method parameters and request contract properties.
 * 
 * The Validation attribute specifies the rules to use for parameter value validation:
 * 
 * */

use Attribute;
use RuntimeException;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Validation {
	 
     /**
      * Inherit validation rules from the model field this property is mapped to.
      * 
      * Defaults to false
      * */
	 public private(set) bool $inherit = false {
	 	 set(bool $value){
	 	 	 $this->inherit = $value;
	 	 }

	 	 get => $this->inherit;
	 }

	 /**
	  * Custom validation rules to be used for valiadtion in addition
	  * to inherited rules or to override rules in model field mapping
	  * 
	  * @var array<string>
	  * */
	 public private(set) array $rules = [] {
	 	 set(array $value){
	 	 	 $this->rules = $value;
	 	 }

	 	 get => $this->rules;
	 }

	 /**
	  * When rules are set to be inherited from the model field mapped
	  * to property, exclude one or more rules from being inherited
	  * by listing them here.
	  * 
	  * @var array<string>
	  * */
	 public private(set) array $except = [] {
	 	 set(array $value){
	 	 	 $this->except = $value;
	 	 }

	 	 get => $this->except;
	 }

	 /**
	  * When rules are set to be inherited from the model field mapped
	  * to property, specify which rules to inherit by listing them here.
	  * 
	  * If this is empty, all rules defined in the mapped model fields
	  * will be inherited
	  * 
	  * @var array<string>
	  * */
	 public private(set) array $only = [] {
	 	 set(array $value){
	 	 	 $this->only = $value;
	 	 }

	 	 get => $this->only;
	 }

	 public function __construct(
	 	 bool  $inherit = false, 
	 	 array $rules   = [],
	 	 array $except  = [],
	 	 array $only    = []
	 ){

         if(!empty($except) && !empty($only)){
         	 throw new RuntimeException("Validation attribute cannot specify both 'only' and 'except'. Choose one!");
         }

	 	 $this->inherit = $inherit;
	 	 $this->rules   = $rules;
	 	 $this->except  = $except;
	 	 $this->only    = $only; 
	 }
}
