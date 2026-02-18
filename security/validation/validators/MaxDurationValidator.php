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
 * Video maximum duration validator
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Abstracts\IValidator;

class MaxDurationValidator extends IValidator {
	
	 public function validate(mixed $value, array $context = []) : ValidationResult {
	 	 
	 	 /**
	 	  * Find video meta data here. FOR LATER
	 	  * */

	 	 $isvalid = true;

	 	 return new ValidationResult(
             isvalid: $isvalid,
             message: $isvalid ? null : "{$field} must be at most {$threshold} seconds!"
         );
	 }
}
