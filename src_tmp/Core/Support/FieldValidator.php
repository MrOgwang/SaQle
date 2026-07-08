<?php
namespace SaQle\Core\Support;

use SaQle\Security\Validation\Types\{
     FieldValidationResult, 
     ValidationMode,
     ValidationAction
};
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

         $ordered_rules = $this->rules;

         uksort($ordered_rules, fn($a, $b) => $app->rules->priority($a) <=> $app->rules->priority($b));

         foreach($ordered_rules as $rule => $threshold){

             //Check if rule exists in registry
             if(!$app->rules->has($rule)){
                 throw new RuntimeException("Validator for rule '{$rule}' is not registered in the app.");
             } 

             $validator_class = app()->rules->get($rule)['validator'];
             $validator = new $validator_class($field, $threshold);
             $result = $validator->validate($value);

             if(!$result->isvalid){
                 $errors[] = $result->message;

                 if($this->mode === ValidationMode::FAIL_FAST) {
                     break;
                 }
             }else{
                 if(!is_null($result->normalized)){
                     $value = $result->normalized;
                 }
             }
            
             if($result->action && $result->action === ValidationAction::STOP){
                 break;
             }
         }

         return new FieldValidationResult($field, empty($errors), $errors, $value);
	 }

     public function get_rules(){
         return $this->rules;
     }
}
