<?php

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Interfaces\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class EmailValidator extends IValidator
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
                "email rule for {$field} must be true or false."
            );
        }

        // 2️⃣ If disabled → always pass
        if ($threshold === false) {
            return new ValidationResult(true, null);
        }

        // 3️⃣ Must be string
        if (!is_string($value) || trim($value) === '') {
            return new ValidationResult(
                false,
                "{$field} must be a valid email address."
            );
        }

        $value = trim($value);

        // 4️⃣ Validate email
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return new ValidationResult(
                false,
                "{$field} must be a valid email address."
            );
        }

        // 5️⃣ Passed
        return new ValidationResult(true, null);
    }
}
