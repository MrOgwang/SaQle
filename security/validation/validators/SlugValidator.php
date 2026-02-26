<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class SlugValidator extends IValidator
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
                "Slug rule for {$field} must be true or false."
            );
        }

        // 2️⃣ If slug rule disabled → always pass
        if ($threshold === false) {
            return new ValidationResult(true, null);
        }

        // 3️⃣ Must be string
        if (!is_string($value)) {
            return new ValidationResult(
                false,
                "{$field} must be a valid slug."
            );
        }

        $value = trim($value);

        // 4️⃣ Empty string fails (slug must contain content)
        if ($value === '') {
            return new ValidationResult(
                false,
                "{$field} must be a valid slug."
            );
        }

        // 5️⃣ Strict slug regex
        $pattern = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

        if (!preg_match($pattern, $value)) {
            return new ValidationResult(
                false,
                "{$field} must contain only lowercase letters, numbers, and single hyphens between words."
            );
        }

        return new ValidationResult(true, null);
    }
}
