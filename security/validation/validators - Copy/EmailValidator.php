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
 * Checks if an input is a valid email address
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Abstracts\IValidator;

class EmailValidator extends IValidator {
	 public function validate(string $field, mixed $value, mixed $threshold = null, array $context = []): ValidationResult {
         if(!is_string($value)){
             return ValidationResult::fail('Invalid email format');
         }

         $normalized = trim(mb_strtolower($value));

         if(!filter_var($normalized, FILTER_VALIDATE_EMAIL)){
             return ValidationResult::fail('Invalid email address');
         }

         return new ValidationResult(
             isvalid:    true,
             normalized: $normalized
         );
     }

     public function stop_on_fail(): bool {
        return true;
     }
}
