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

     private function evaluate_rule($app, $rule, $field, $threshold, $value){

         //rule class must exist in registry
         if(!$app->rules->has($rule)){
             throw new RuntimeException("Validator for rule '{$rule}' is not registered in the app.");
         } 

         $validator_class = $app->rules->get($rule)['validator'];

         $validator = new $validator_class($field, $threshold);

         return $validator->validate($value);
     }

     private function process_result($result, $value){

         $errors = [];
         $exit   = false;

         if(!$result->isvalid){

             $errors[] = $result->message;

             if($this->mode === ValidationMode::FAIL_FAST){
                 $exit = true;
             }

         }else{

             if(!is_null($result->normalized)){
                 $value = $result->normalized;
             }
         }
        
         if($result->action && $result->action === ValidationAction::STOP){
             $exit = true;
         }

         return [$errors, $value, $exit];
     }

	 public function validate(string $field, mixed $value) : FieldValidationResult {

         $app = app();

         $errors = [];

         //validate required rule first. Its important.
         if(array_key_exists('required', $this->rules)){

             $result = $this->evaluate_rule($app, 'required', $field, $this->rules['required'], $value);

             [$errs, $value, $exit] = $this->process_result($result, $value);

             $errors = array_merge($errors, $errs);

             if($exit){
                 return new FieldValidationResult($field, empty($errors), $errors, $value);
             }

             unset($this->rules['required']);

         }

         if($this->array){

             if(!is_array($value)){
                 throw new RuntimeException("The value provided is not an array!");
             }

             return (new ArrayItemValidator())->validate($field, $value, $this->rules);
         } 

         $ordered_rules = $this->rules;

         uksort($ordered_rules, fn($a, $b) => $app->rules->priority($a) <=> $app->rules->priority($b));

         foreach($ordered_rules as $rule => $threshold){

             $result = $this->evaluate_rule($app, $rule, $field, $threshold, $value);

             [$errs, $value, $exit] = $this->process_result($result, $value);

             $errors = array_merge($errors, $errs);

             if($exit){
                 break;
             }

         }

         return new FieldValidationResult($field, empty($errors), $errors, $value);
	 }

     public function get_rules(){
         return $this->rules;
     }
}
