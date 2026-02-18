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
 * Minimum time validator
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Abstracts\{IValidator, AbstractTimeValidator};
use SaQle\Security\Validation\Utils\DateValidationHelper;

class MinTimeValidator extends AbstractTimeValidator {
     use DateValidationHelper;

     public function validate(mixed $value, array $context = []): ValidationResult {
         return $this->validate_temporal("min", $value, $context);
     }

     public function message() : string {
         return "{$this->field} must be on or after {$this->threshold}";
     }

}
