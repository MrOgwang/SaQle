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
 * Maximum length validator for strings
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Abstracts\IValidator;

class MaxLengthValidator extends IValidator {

	 protected function threshold_type() : string {
         return 'int';
     }
	
	 public function validate(mixed $value, array $context = []) : ValidationResult {

	 	 if(mb_strlen($value ?? "") <= $this->threshold){
	 	 	 return new ValidationResult(true);
	 	 }
	 	
	 	 return new ValidationResult(false, "{$this->field} must be at most {$this->threshold} characters!");
	 }
}
