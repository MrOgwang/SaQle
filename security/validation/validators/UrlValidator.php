<?php

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Interfaces\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class UrlValidator extends IValidator
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
                "url rule for {$field} must be true or false."
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
                "{$field} must be a valid URL."
            );
        }

        $value = trim($value);

        // 4️⃣ Validate URL
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return new ValidationResult(
                false,
                "{$field} must be a valid URL."
            );
        }

        // ✅ Passed
        return new ValidationResult(true, null);
    }
}
