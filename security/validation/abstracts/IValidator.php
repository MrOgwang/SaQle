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
 * Ivalidaor interface
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation\Abstracts;

use SaQle\Security\Validation\Types\ValidationResult;

abstract class IValidator {

     protected string $field;

     protected mixed  $threshold;

     public function __construct(string $field, mixed $threshold){
         $this->field = $field;
         $this->threshold = $threshold;
     }

	 abstract public function validate(mixed $value, array $context = []): ValidationResult;
     
     public function stop_on_fail() : bool {
     	 return false;
     }

     public function message() : string {
         return "";
     }
}
