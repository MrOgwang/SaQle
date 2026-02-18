<?php
namespace SaQle\Core\Support;

use SaQle\Security\Validation\Types\{FieldValidationResult, ValidationMode, RuleKey};
use SaQle\Security\Validation\Utils\ArrayItemValidator;
use RuntimeException;

class FieldValidator {

     public function __construct(
         protected array $rules,
         protected ValidationMode $mode = ValidationMode::COLLECT_ALL
     ){}

     private function parse_rule_key(string $key): RuleKey {
         if(str_ends_with($key, '.*')){
             return new RuleKey(base: substr($key, 0, -2), is_wildcard: true);
         }

         return new RuleKey($key, false);
     }

	 public function validate(string $field, mixed $value) : FieldValidationResult {

         $errors = [];

         $parsed = parse_rule_key($field);

         if($parsed->is_wildcard){
             if(!is_array($value)){
                 throw new RuntimeException("The value provided is not an array!");
             }

             return (new ArrayItemValidator())->validate($field, $value, $rules);
         }

         foreach($this->rules as $r => $v){
             $validator_class = app()->rules->get($r);
             $validator = new $validator_class();
             $result = $validator->validate($field, $v, $value, $rules);

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
