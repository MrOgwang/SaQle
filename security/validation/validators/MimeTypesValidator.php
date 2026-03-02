<?php
namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Core\Files\UploadedFile;
use finfo;

class MimeTypesValidator extends IValidator {

     protected function threshold_type() : string {
        return 'array';
     }

     public function validate(mixed $value, array $context = []) : ValidationResult {

         //Normalize allowed MIME types
         $allowed = array_map(fn($type) => strtolower(trim((string)$type)), $this->threshold);

         //Resolve file path
         $file_path = null;

         if($value instanceof UploadedFile){
             $file_path = $value->tmp_name;
         }elseif (is_string($value) && file_exists($value)) {
             $file_path = $value;
         }

         if(!$file_path || !file_exists($file_path)){
             return new ValidationResult(false, "{$this->field} must be a valid file.");
         }

         //Detect real MIME type using finfo
         $finfo = new finfo(FILEINFO_MIME_TYPE);
         $detected_mime = $finfo->file($file_path);

         if(!$detected_mime) {
             return new ValidationResult(false, "{$this->field} MIME type could not be determined.");
         }

         $detected_mime = strtolower($detected_mime);

         //Extract major type (e.g. "image" from "image/jpeg")
         [$detected_major] = explode('/', $detected_mime, 2);

         $is_valid = false;

         foreach($allowed as $type){

             //Wildcard support: image/*, video/*
             if(str_ends_with($type, '/*')){
                 $allowed_major = rtrim($type, '/*');
                 if($allowed_major === $detected_major){
                     $is_valid = true;
                     break;
                 }
             }else{
                 //Exact match
                 if($type === $detected_mime){
                     $is_valid = true;
                     break;
                 }
             }
         }

         if(!$is_valid){
             return new ValidationResult(
                 false, 
                 "{$this->field} must be one of the following MIME types: ".implode(', ', $allowed)."."
             );
         }

         return new ValidationResult(true, null);
     }
}