<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\CharField;
use SaQle\Orm\Entities\Field\Attributes\{
	 FieldDefinition, 
	 ShouldValidate,
	 FormControl
};
use SaQle\Auth\Interfaces\HashServiceInterface;

class PasswordField extends CharField {
	 //the miminum strength
	 #[ShouldValidate()]
	 protected ?int $min_strength = null;

	 public function min_strength(int $min_strength){
	 	 $this->min_strength = $min_strength;
	 	 return $this;
	 }

	 public function get_min_strength(){
	 	 return $this->min_strength;
	 }

	 protected function initialize_defaults(){

         /**
          * This will automatically hash
          * the password using the hashing algorighm 
          * specified in the auth config file
          * */
	 	 $this->transform(function($value, $model){
	 	 	 $hash_service = resolve(HashServiceInterface::class);
	 	 	 return $hash_service->make($value);
	 	 });

	 	 /**
	 	  * Password fields must remain masked by default
	 	  * */
	 	 $this->render(function($value, $model){
			 	 return "******";
		 });

	 	 if(!$this->control_type){
	 	 	 $this->control_type = "password";
	 	 }

		 parent::initialize_defaults();
     }
}

