<?php

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Interfaces\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class PhoneValidator extends IValidator
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
                "phone rule for {$field} must be true or false."
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
                "{$field} must be a valid phone number."
            );
        }

        $value = trim($value);

        // 4️⃣ Basic phone number regex (digits, optional +, spaces, -, ())
        // Minimum 6 digits, maximum 15 digits
        $digits = preg_replace('/\D+/', '', $value); // remove non-digits

        if (strlen($digits) < 6 || strlen($digits) > 15) {
            return new ValidationResult(
                false,
                "{$field} must contain between 6 and 15 digits."
            );
        }

        // Optional: Check allowed characters
        if (!preg_match('/^\+?[0-9\s\-\(\)]+$/', $value)) {
            return new ValidationResult(
                false,
                "{$field} contains invalid characters."
            );
        }

        // ✅ Passed
        return new ValidationResult(true, null);
    }
}
