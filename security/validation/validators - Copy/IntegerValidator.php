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
 * The float validator checks that the value is of type integer.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Abstracts\IValidator;

class IntegerValidator extends IValidator {
	
	 public function validate(string $field, mixed $value, mixed $threshold = null, array $context = []) : ValidationResult {

	 	 if(!is_numeric($value)){
	 	 	 return ValidationResult::fail("The value provided is not a number!");
	 	 }

	 	 $isvalid = settype($value, "integer") ? true : false;
	 	 return new ValidationResult(
             isvalid: $isvalid,
             message: $isvalid ? null : "The value provided is not an integer!"
         );

	 }

	 public function stop_on_fail(): bool {
        return true;
     }
}
