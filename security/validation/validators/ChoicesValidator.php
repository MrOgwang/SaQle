<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class ChoicesValidator extends IValidator
{
    public function validate(
        string $field,
        mixed $value,
        mixed $threshold = null,
        array $context = []
    ): ValidationResult {

        // 1️⃣ Threshold must be non-empty array
        if (!is_array($threshold) || empty($threshold)) {
            return new ValidationResult(
                false,
                "choices rule for {$field} must be a non-empty array."
            );
        }

        // 2️⃣ Validate value exists in choices
        if (!in_array($value, $threshold, true)) {
            return new ValidationResult(
                false,
                "{$field} must be one of the following values: " . implode(', ', $threshold)
            );
        }

        // 3️⃣ Passed
        return new ValidationResult(true, null);
    }
}
