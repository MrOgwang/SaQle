<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Core\Files\UploadedFile;

class ExtensionsValidator extends IValidator {
     protected function threshold_type() : string {
        return 'array';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {

         //Normalize allowed extensions (lowercase, no dot)
         $allowed = array_map(fn($ext) => ltrim(strtolower(trim((string)$ext)), '.'), $this->threshold);

         //Resolve filename
         $filename = null;

         if($value instanceof UploadedFile){
             $filename = $value->name;
         }elseif(is_string($value)){
             $filename = basename($value);
         }

         if(!$filename){
             return new ValidationResult(false, "{$this->field} must be a valid file.");
         }

         //Extract extension safely
         $extension = pathinfo($filename, PATHINFO_EXTENSION);

         if(!$extension){
             return new ValidationResult(false, "{$this->field} must have a valid file extension.");
         }

         $extension = strtolower($extension);

         //Validate against allowed list
         if(!in_array($extension, $allowed, true)){
             return new ValidationResult(
                 false,
                 "{$this->field} must have one of the following extensions: ".implode(', ', $allowed)."."
             );
         }

         return new ValidationResult(true, null);
     }
}
