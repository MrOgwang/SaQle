<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class InternationalPhoneValidator extends IValidator
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
                "International rule for {$field} must be true or false."
            );
        }

        // 2️⃣ If disabled → always pass
        if ($threshold === false) {
            return new ValidationResult(true, null);
        }

        // 3️⃣ Must be string
        if (!is_string($value)) {
            return new ValidationResult(
                false,
                "{$field} must be a valid international phone number."
            );
        }

        $value = trim($value);

        // 4️⃣ Empty fails
        if ($value === '') {
            return new ValidationResult(
                false,
                "{$field} must be a valid international phone number."
            );
        }

        // 5️⃣ Strict E.164 validation
        $pattern = '/^\+[1-9]\d{7,14}$/';

        if (!preg_match($pattern, $value)) {
            return new ValidationResult(
                false,
                "{$field} must be in international format (E.164), e.g. +254712345678."
            );
        }

        return new ValidationResult(true, null);
    }
}
