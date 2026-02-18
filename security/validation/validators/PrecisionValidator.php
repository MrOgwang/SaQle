<?php

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Interfaces\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class PrecisionValidator extends IValidator
{
    public function validate(
        string $field,
        mixed $value,
        mixed $threshold = null,
        array $context = []
    ): ValidationResult {

        // 1️⃣ Threshold must be integer >= 1
        if (!is_int($threshold) || $threshold < 1) {
            return new ValidationResult(
                false,
                "precision rule for {$field} must be a positive integer."
            );
        }

        // 2️⃣ Value must be numeric
        if (!is_numeric($value)) {
            return new ValidationResult(
                false,
                "{$field} must be numeric."
            );
        }

        $valueStr = preg_replace('/[^0-9]/', '', (string)$value); // remove decimal & sign

        $digits = strlen($valueStr);

        if ($digits > $threshold) {
            return new ValidationResult(
                false,
                "{$field} must have at most {$threshold} total digits."
            );
        }

        return new ValidationResult(true, null);
    }
}
