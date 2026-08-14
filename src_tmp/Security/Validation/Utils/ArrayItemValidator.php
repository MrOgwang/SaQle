<?php

namespace SaQle\Security\Validation\Utils;

use SaQle\Security\Validation\Types\ArrayValidationMode;
use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Core\Support\FieldValidator;
use SaQle\Security\Validation\Types\FieldValidationResult;

class ArrayItemValidator {

     public function __construct(
         private ArrayValidationMode $mode = ArrayValidationMode::ALL_ITEMS_MUST_PASS
     ){}

     public function validate(string $field, mixed $value, array $rules): FieldValidationResult {

         $errors = [];
         $passed = 0;
         $normalized = [];

         foreach($value as $index => $item){

             $result = (new FieldValidator($rules, false))->validate(field: "{$field}.{$index}", value: $item);

             if(!$result->isvalid){ 
                 $errors[$index] = $result->errors;
             }else{
                 $normalized[] = $result->normalized;
                 $passed++;
             }

             if($this->mode === ArrayValidationMode::AT_LEAST_ONE_MUST_PASS && $passed > 0){
                 return new FieldValidationResult($field, true, $errors, $normalized);
             }
         }

         if($this->mode === ArrayValidationMode::ALL_ITEMS_MUST_PASS && !empty($errors)){
             return new FieldValidationResult($field, false, $errors, $normalized);
         }

         return new FieldValidationResult($field, $passed > 0, $errors, $normalized);
     }
}
