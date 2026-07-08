<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Core\Files\UploadedFile;

class MaxSizeValidator extends IValidator {
    
     protected function threshold_type(): string {
         return 'int'; // value provided in MB
     }

     public function validate(mixed $value, array $context = []): ValidationResult {

         if(!$value instanceof UploadedFile){
             return new ValidationResult(false, "{$this->field} must be a valid uploaded file.");
         }

         if(!isset($value->size) || !is_numeric($value->size)){
             return new ValidationResult(false, "{$this->field} file size could not be determined.");
         }

         $max_mb = (int)$this->threshold;
         $max_bytes = $max_mb * 1024 * 1024;

         $size = (int)$value->size;

         if($size > $max_bytes){
             return new ValidationResult(false, "{$this->field} must not exceed {$max_mb}MB.");
         }

         return new ValidationResult(true, null);
     }
}