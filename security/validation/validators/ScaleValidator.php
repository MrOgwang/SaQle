<?php

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Interfaces\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class ScaleValidator extends IValidator
{
    public function validate(
        string $field,
        mixed $value,
        mixed $threshold = null,
        array $context = []
    ): ValidationResult {

        // 1️⃣ Threshold must be integer >= 0
        if (!is_int($threshold) || $threshold < 0) {
            return new ValidationResult(
                false,
                "scale rule for {$field} must be a non-negative integer."
            );
        }

        // 2️⃣ Value must be numeric
        if (!is_numeric($value)) {
            return new ValidationResult(
                false,
                "{$field} must be numeric."
            );
        }

        $valueStr = (string)$value;

        // 3️⃣ Count digits after decimal
        $parts = explode('.', $valueStr);
        $scaleDigits = isset($parts[1]) ? strlen(rtrim($parts[1], '0')) : 0;

        if ($scaleDigits > $threshold) {
            return new ValidationResult(
                false,
                "{$field} must have at most {$threshold} digits after the decimal point."
            );
        }

        return new ValidationResult(true, null);
    }
}
