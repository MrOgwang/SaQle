<?php
namespace SaQle\Orm\Entities\Model\Schema;

class FormDefinition {
	
     public function __construct(
     	 /**
     	  * All model form fields. This is a single source of truth.
     	  * 
     	  * They are tracked separately because a model
     	  * will have the same form fields no matter the number
     	  * of forms declared on it.
     	  * */
     	 public readonly array $all_fields,

         //all the forms declared on a model
     	 public readonly array $forms
     ){}
}