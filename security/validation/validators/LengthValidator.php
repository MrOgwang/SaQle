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
 * Length validator for strings
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Abstracts\IValidator;

class LengthValidator extends IValidator {
	
	 public function validate(mixed $value, array $context = []) : ValidationResult {
	 	 
	 	 $isvalid = mb_strlen($value) === (int)$threshold ? true : false;

	 	 return new ValidationResult(
             isvalid: $isvalid,
             message: $isvalid ? null : "{$field} must have exactly {$threshold} characters!"
         );
	 }
}
