<?php

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Interfaces\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class MinStrengthValidator extends IValidator
{
    public function validate(
        string $field,
        mixed $value,
        mixed $threshold = null,
        array $context = []
    ): ValidationResult {

        // 1️⃣ Threshold must be integer between 1 and 5
        if (!is_int($threshold) || $threshold < 1 || $threshold > 5) {
            return new ValidationResult(
                false,
                "min_strength rule for {$field} must be an integer between 1 and 5."
            );
        }

        // 2️⃣ Must be string
        if (!is_string($value)) {
            return new ValidationResult(
                false,
                "{$field} must be a valid password."
            );
        }

        $value = trim($value);

        if ($value === '') {
            return new ValidationResult(
                false,
                "{$field} must be a valid password."
            );
        }

        $score = 0;
        $length = strlen($value);

        // Base length requirement
        if ($length >= 8) {
            $score++;
        }

        if (preg_match('/[a-z]/', $value)) {
            $score++;
        }

        if (preg_match('/[A-Z]/', $value)) {
            $score++;
        }

        if (preg_match('/\d/', $value)) {
            $score++;
        }

        if (preg_match('/[^a-zA-Z\d]/', $value)) {
            $score++;
        }

        // Bonus for long passwords
        if ($length >= 12) {
            $score++;
        }

        // Cap score at 5
        $score = min($score, 5);

        if ($score < $threshold) {
            return new ValidationResult(
                false,
                "{$field} does not meet the required strength level ({$threshold})."
            );
        }

        return new ValidationResult(true, null);
    }
}
