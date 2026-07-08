<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Utils\MediaValidationHelper;

class MinHeightValidator extends IValidator {

     use MediaValidationHelper;

     protected function threshold_type(): string {
         return 'int';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {

         $min = (int)$this->threshold;
         $path = $value->tmp_name;

         if(($context['media_type'] ?? null) === 'image'){
             [, $height] = getimagesize($path);
         }else{
             $meta = $this->get_video_meta_data($path);
             if(!$meta) return new ValidationResult(false, "{$this->field} video metadata not readable.");
             $height = (int)$meta['height'];
         }

         if($height < $min){
             return new ValidationResult(false, "{$this->field} height must be at least {$min}px.");
         }

         return new ValidationResult(true, null);
     }
}
