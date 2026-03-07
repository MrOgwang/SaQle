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

         return match($this->threshold){
             'integer' => $this->validate_integer($value, $context),
             'string'  => $this->validate_string($value, $context),
             'float'   => $this->validate_float($value, $context),
             'file'    => $this->validate_file($value, $context),
             default   => new ValidationResult(false, "Unsupported native type.")
         };
     }

     private function validate_integer(mixed $value, array $context): ValidationResult {
         return is_int($value) ? new ValidationResult(true, null) : new ValidationResult(false, "{$this->field} must be an integer.");
     }

     private function validate_string(mixed $value, array $context): ValidationResult {
         return is_string($value) ? new ValidationResult(true, null) : new ValidationResult(false, "{$this->field} must be a string.");
     }

     private function validate_float(mixed $value, array $context): ValidationResult {
         return is_float($value) ? new ValidationResult(true, null) : new ValidationResult(false, "{$this->field} must be a float.");
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
