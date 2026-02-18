<?php

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Interfaces\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class JsonValidator extends IValidator
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
                "json rule for {$field} must be true or false."
            );
        }

        // 2️⃣ Disabled → always pass
        if ($threshold === false) {
            return new ValidationResult(true, null);
        }

        // 3️⃣ Value must be string
        if (!is_string($value) || trim($value) === '') {
            return new ValidationResult(
                false,
                "{$field} must be a valid JSON string."
            );
        }

        $value = trim($value);

        // 4️⃣ Validate JSON
        json_decode($value);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new ValidationResult(
                false,
                "{$field} must be valid JSON. Error: " . json_last_error_msg()
            );
        }

        // 5️⃣ Passed
        return new ValidationResult(true, null);
    }
}
