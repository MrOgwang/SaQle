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
 * Timezone validator
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Types\ValidationResult;
use SaQle\Security\Validation\Abstracts\{IValidator, AbstractDateTimeValidator};
use SaQle\Security\Validation\Utils\DateValidationHelper;

class TimezoneValidator extends IValidator {
     use DateValidationHelper;

     public function validate(mixed $value, array $context = []): ValidationResult {
         return $this->validate_temporal_format($value, $context);
     }
}
