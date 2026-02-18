<?php

namespace SaQle\Security\Validation\Utils;

use SaQle\Security\Validation\Types\ArrayValidationMode;
use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Core\Support\FieldValidator;

class ArrayItemValidator {

     public function __construct(
         private ArrayValidationMode $mode = ArrayValidationMode::ALL_ITEMS_MUST_PASS
     ){}

     public function validate(string $field, mixed $value, array $rules): FieldValidationResult {
         $errors = [];
         $passed = 0;

         foreach ($value as $index => $item){
             $result = (new FieldValidator())->validate(field: "{$field}.{$index}", value: $item, rules: $rules);
             if(!$result->isvalid){
                 $errors[$index] = $result->errors;
             }else{
                 $passed++;
             }

             if($this->mode === ArrayValidationMode::AT_LEAST_ONE_MUST_PASS && $passed > 0){
                 return new FieldValidationResult($field, true, $errors);
             }
         }

         if($this->mode === ArrayValidationMode::ALL_ITEMS_MUST_PASS && !empty($errors)){
             return new FieldValidationResult($field, false, $errors);
         }

         return new FieldValidationResult($field, $passed > 0, $errors);
     }
}
