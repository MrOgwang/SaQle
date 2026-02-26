<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class BlacklistValidator extends IValidator
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
                "blacklist rule for {$field} must be a non-empty array."
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

        // 4️⃣ Normalize blacklist
        $blacklist = array_map(
            fn($item) => strtolower(trim((string)$item)),
            $threshold
        );

        $domain = substr(strrchr($value, "@"), 1); // domain only

        foreach ($blacklist as $blocked) {
            if (str_starts_with($blocked, '@')) {
                // Domain block
                if ($domain === ltrim($blocked, '@')) {
                    return new ValidationResult(
                        false,
                        "{$field} is blacklisted."
                    );
                }
            } else {
                // Exact email block
                if ($value === $blocked) {
                    return new ValidationResult(
                        false,
                        "{$field} is blacklisted."
                    );
                }
            }
        }

        // Not blacklisted → pass
        return new ValidationResult(true, null);
    }
}
