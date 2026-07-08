<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Utils\MediaValidationHelper;

class MaxWidthValidator extends IValidator {

     use MediaValidationHelper;

     protected function threshold_type(): string {
         return 'int';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {

         $max = (int)$this->threshold;
         $path = $value->tmp_name;

         if(($context['media_type'] ?? null) === 'image'){
             [$width] = getimagesize($path);
         }else{
             $meta = $this->get_video_meta_data($path);
             if(!$meta) return new ValidationResult(false, "{$this->field} video metadata not readable.");
             $width = (int)$meta['width'];
         }

         if($width > $max){
             return new ValidationResult(false, "{$this->field} width must not exceed {$max}px.");
         }

         return new ValidationResult(true, null);
     }
}
