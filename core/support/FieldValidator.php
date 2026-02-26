<?php
namespace SaQle\Core\Support;

use SaQle\Security\Validation\Types\{FieldValidationResult, ValidationMode};
use SaQle\Security\Validation\Utils\ArrayItemValidator;
use RuntimeException;

class FieldValidator {

     public function __construct(
         protected array $rules,
         protected bool  $array = false,
         protected ValidationMode $mode = ValidationMode::COLLECT_ALL
     ){}

	 public function validate(string $field, mixed $value) : FieldValidationResult {

         $errors = [];

         if($this->array){
             if(!is_array($value)){
                 throw new RuntimeException("The value provided is not an array!");
             }

             return (new ArrayItemValidator())->validate($field, $value, $this->rules);
         }

         $app = app();

         foreach($this->rules as $rule => $threshold){

             //Check if rule exists in registry
             if(!$app->rules->has($rule)){
                 throw new RuntimeException("Validator for rule '{$rule}' is not registered in the app.");
             }

             $validator_class = app()->rules->get($rule);
             $validator = new $validator_class($field, $threshold);
             $result = $validator->validate($value);

             if(!$result->isvalid){
                 $errors[] = $result->message;

                 if($mode === ValidationMode::FAIL_FAST || $validator->stop_on_fail()){
                     break;
                 }
             }
         }

         return new FieldValidationResult($field, empty($errors), $errors);
	 }

     public function get_rules(){
         return $this->rules;
     }
}
