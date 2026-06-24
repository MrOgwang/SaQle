<?php
namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class NativeTypeValidator extends IValidator {

     protected function threshold_type(): string { 
         return 'string';
     }

     private const SUPPORTED_TYPES = [
         'integer',
         'string',
         'float',
         'file'
     ];

     public function validate(mixed $value, array $context = []): ValidationResult {
         //threshold must be valid type
         if(!is_string($this->threshold) || !in_array($this->threshold, self::SUPPORTED_TYPES, true)){
             return new ValidationResult(false, "Invalid type for {$this->field}.");
         }
         
         $result = match($this->threshold){
             'integer' => $this->validate_integer($value, $context),
             'string'  => $this->validate_string($value, $context),
             'float'   => $this->validate_float($value, $context),
             'file'    => $this->validate_file($value, $context),
             default   => new ValidationResult(false, "Unsupported native type.")
         };

         return $result;
     }

     private function validate_integer(mixed $value, array $context): ValidationResult {

         if(is_int($value)){
             return new ValidationResult(true, null, $value);
         }

         if(is_string($value) && filter_var($value, FILTER_VALIDATE_INT) !== false){
             return new ValidationResult(true, null, (int)$value);
         }

         return new ValidationResult(false, "{$this->field} must be an integer.");
     }

     private function validate_string(mixed $value, array $context): ValidationResult {

         if(is_string($value)){
             return new ValidationResult(true, null, $value);
         }

         if(is_scalar($value)){
             return new ValidationResult(true, null, (string)$value);
         }

         return new ValidationResult(false, "{$this->field} must be a string.");
     } 

     private function validate_float(mixed $value, array $context): ValidationResult {
         
         if(is_float($value)){
             return new ValidationResult(true, null, $value);
         }

         if(is_numeric($value)){
             return new ValidationResult(true, null, (float)$value);
         }

         return new ValidationResult(false, "{$this->field} must be a float.");
     }

     private function validate_file(mixed $value, array $context) : ValidationResult {
         /*if (!is_array($value)) {
            return new ValidationResult(false, "{$field} must be a valid uploaded file.");
         }

         $requiredKeys = ['name', 'type', 'tmp_name', 'error', 'size'];

         foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $value)) {
                return new ValidationResult(false, "{$field} is not a valid file structure.");
            }
         }

         if ($value['error'] !== UPLOAD_ERR_OK) {
            return new ValidationResult(false, "{$field} upload failed.");
         }

         $skipUploadCheck = $context['skip_upload_check'] ?? false;

         if (!$skipUploadCheck && !is_uploaded_file($value['tmp_name'])) {
            return new ValidationResult(false, "{$field} is not a valid uploaded file.");
         }*/

        return new ValidationResult(true, null);
    }
}
