<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * Video duration validator
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Utils\MediaValidationHelper;

class DurationValidator extends IValidator {
	
	 use MediaValidationHelper;

	 protected function threshold_type(): string {
	     return 'int'; // seconds
     }

     public function validate(mixed $value, array $context = []): ValidationResult {

	     if(($context['media_type'] ?? null) !== 'video'){
	         return new ValidationResult(true, null);
	     }

	     $meta = $this->get_video_meta_data($value->tmp_name);
	     if(!$meta) return new ValidationResult(false, "{$this->field} video metadata not readable.");

	     $duration = (int)floor($meta['duration']);

	     if($duration !== $this->threshold){
	         return new ValidationResult(false, "{$this->field} duration must be exactly {$this->threshold} seconds.");
	     }

	     return new ValidationResult(true, null);
	 }

}
