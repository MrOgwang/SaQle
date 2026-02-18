<?php

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Interfaces\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class UnsignedValidator extends IValidator
{
    public function validate(
        string $field,
        mixed $value,
        mixed $threshold = null,
        array $context = []
    ): ValidationResult {

        // 1️⃣ Threshold must be boolean
        if (!is_bool($threshold)) {
            return new ValidationResult(
                false,
                "Unsigned rule for {$field} must be true or false."
            );
        }

        // 2️⃣ Value must be numeric (int or float)
        if (!is_int($value) && !is_float($value)) {
            return new ValidationResult(
                false,
                "{$field} must be a numeric value."
            );
        }

        // 3️⃣ If unsigned = false → allow everything
        if ($threshold === false) {
            return new ValidationResult(true, null);
        }

        // 4️⃣ unsigned = true → disallow negatives
        if ($value < 0) {
            return new ValidationResult(
                false,
                "{$field} must be an unsigned number."
            );
        }

        return new ValidationResult(true, null);
    }
}
