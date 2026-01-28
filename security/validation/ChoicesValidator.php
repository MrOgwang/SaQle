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
 * Checks if an input is among the recommended choices
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Interfaces\IValidator;

class ChoicesValidator extends IValidator {
	
	 public function validate(string $field, mixed $value, mixed $threshold = null, array $context = []) : ValidationResult {
	 	 
	 	 $isvalid = in_array($value, array_keys($threshold));
	 	 return new ValidationResult(
             isvalid: $isvalid,
             message: $isvalid ? null : "{$field} is not among the options allowed. Options include[".implode(', ', array_keys($threshold))."]"
         );

	 }

	 public function stop_on_fail(): bool {
        return true;
     }
}

