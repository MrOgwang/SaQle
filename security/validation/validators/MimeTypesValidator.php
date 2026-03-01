<?php
namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Core\Files\UploadedFile;
use finfo;

class MimeTypesValidator extends IValidator {
     protected function threshold_type(): string {
         return 'array';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {

         //normalize allowed MIME types
         $allowed = array_map(fn($type) => strtolower(trim((string)$type)), $this->threshold);

         //resolve file path
         $file_path = null;

         if($value instanceof UploadedFile){
             $file_path = $value->tmp_name;
         }elseif (is_string($value) && file_exists($value)){
             $file_path = $value;
         }

         if(!$file_path || !file_exists($file_path)){
             return new ValidationResult(false, "{$this->field} must be a valid file.");
         }

         //Detect real MIME type using finfo
         $finfo = new finfo(FILEINFO_MIME_TYPE);
         $detected_mime = $finfo->file($file_path);

         if(!$detected_mime){
             return new ValidationResult(false, "{$this->field} MIME type could not be determined.");
         }

         $detected_mime = strtolower($detected_mime);

         //Validate against allowed list
         if(!in_array($detected_mime, $allowed, true)){
             return new ValidationResult(false, "{$field} must be one of the following MIME types: ".implode(', ', $allowed) . ".");
         }

         return new ValidationResult(true, null);
     }
}
