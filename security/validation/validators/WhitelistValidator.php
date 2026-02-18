<?php

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Interfaces\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class WhitelistValidator extends IValidator
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
                "whitelist rule for {$field} must be a non-empty array."
            );
        }

        // 2️⃣ Value must be string
        if (!is_string($value) || trim($value) === '') {
            return new ValidationResult(
                false,
                "{$field} must be a valid email."
            );
        }

        $value = strtolower(trim($value));

        // 3️⃣ Validate email structure first
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return new ValidationResult(
                false,
                "{$field} must be a valid email address."
            );
        }

        // 4️⃣ Normalize whitelist
        $whitelist = array_map(
            fn($item) => strtolower(trim((string)$item)),
            $threshold
        );

        // 5️⃣ Check whitelist
        $domain = substr(strrchr($value, "@"), 1); // domain only

        foreach ($whitelist as $allowed) {
            if (str_starts_with($allowed, '@')) {
                // Domain match
                if ($domain === ltrim($allowed, '@')) {
                    return new ValidationResult(true, null);
                }
            } else {
                // Exact email match
                if ($value === $allowed) {
                    return new ValidationResult(true, null);
                }
            }
        }

        return new ValidationResult(
            false,
            "{$field} is not in the allowed whitelist."
        );
    }
}
