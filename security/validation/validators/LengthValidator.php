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
namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Abstracts\IValidator;

class LengthValidator extends IValidator {

	 protected function threshold_type(): string {
         return 'int';
     }
	
	 public function validate(mixed $value, array $context = []) : ValidationResult {
	 	 
	 	 $isvalid = mb_strlen($value) === (int)$this->threshold ? true : false;

	 	 return new ValidationResult(
             isvalid: $isvalid,
             message: $isvalid ? null : "{$this->field} must have exactly {$this->threshold} characters!"
         );
	 }
}
