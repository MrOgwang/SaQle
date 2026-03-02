<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Utils\MediaValidationHelper;

class AspectRatioValidator extends IValidator {

     use MediaValidationHelper;

     protected function threshold_type(): string { 
         return 'array'; // Example: [16, 9]
     }

     public function validate(mixed $value, array $context = []): ValidationResult {
         //validate threshold
         if(!is_array($this->threshold) || count($this->threshold) !== 2 || !is_numeric($this->threshold[0]) ||
            !is_numeric($this->threshold[1]) || $this->threshold[0] <= 0 || $this->threshold[1] <= 0
         ){
             return new ValidationResult(
                 false,
                 "Aspect ratio rule for {$this->field} must be [width_ratio, height_ratio]."
             );
         }

         $path = $value->tmp_name;

         if(($context['media_type'] ?? null) === 'image'){
             [$width, $height] = getimagesize($path);
         }else{
             $meta = $this->get_video_meta_data($path);
             if(!$meta) return new ValidationResult(false, "{$this->field} video metadata not readable.");
             $width = (int)$meta['width'];
             $height = (int)$meta['height'];
         }

         [$w_ratio, $h_ratio] = ...$this->threshold;

         if($width * $h_ratio !== $height * $w_ratio){
             $ratio_string = implode(":", $this->threshold);
             return new ValidationResult(false, "{$this->field} must have aspect ratio [{$ratio_string}].");
         }

         return new ValidationResult(true, null);
     }
}
