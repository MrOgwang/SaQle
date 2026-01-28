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
namespace SaQle\Security\Validation\Interfaces;

use SaQle\Security\Validation\Types\ValidationResult;

interface IValidator {
	 public function validate(string $field, mixed $value, mixed $threshold = null, array $context = []): ValidationResult;
     public function stop_on_fail() : bool{
     	 return false;
     }
}
