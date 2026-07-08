<?php
namespace SaQle\Core\Support;

use RuntimeException;

class Validator {

     protected static array $errors = [];

     public static function validate(string $rule, string $field, mixed $value) : bool {
         $app = app();

         //Check if rule exists in registry
         if(!$app->rules->has($rule)){
             throw new RuntimeException("Validator for rule '{$rule}' is not registered in the app.");
         }

         $validator_class = app()->rules->get($rule);
         $validator = new $validator_class($field, true);
         $result = $validator->validate($value);
         
         if(!$result->isvalid){
             self::$errors[$field] = $result->message;
         }

         return $result->isvalid;
     }

     public static function last_message(?string $field = null): ? string {
         if($field !== null){
             return self::$errors[$field] ?? null;
         }

         return empty(self::$errors) ? null : end(self::$errors);
     }

     public static function all_messages(): array {
         return self::$errors;
     }

     public static function clear(): void {
         self::$errors = [];
     }
}