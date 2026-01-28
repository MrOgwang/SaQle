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
 * Checks if a text input is empty
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Interfaces\IValidator;

class EmptyValidator extends IValidator {
	
	 public function validate(string $field, mixed $value, mixed $threshold = null, array $context = []) : ValidationResult {
	 	 
	 	 $isvalid = (bool)$threshold === false && trim((string)$value) == "" ? false : true;
	 	 return new ValidationResult(
             isvalid: $isvalid,
             message: $isvalid ? null : "{$field} cannot be less than zero!"
         );
	 }
}

