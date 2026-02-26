<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class SchemesValidator extends IValidator
{
    public function validate(
        string $field,
        mixed $value,
        mixed $threshold = null,
        array $context = []
    ): ValidationResult {

        // 1️⃣ Threshold must be a non-empty array
        if (!is_array($threshold) || empty($threshold)) {
            return new ValidationResult(
                false,
                "Schemes rule for {$field} must be a non-empty array."
            );
        }

        // Normalize allowed schemes to lowercase
        $allowedSchemes = array_map(
            fn($scheme) => strtolower(trim((string)$scheme)),
            $threshold
        );

        // 2️⃣ Value must be string
        if (!is_string($value) || trim($value) === '') {
            return new ValidationResult(
                false,
                "{$field} must be a valid URL."
            );
        }

        $value = trim($value);

        // 3️⃣ Validate URL structure
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return new ValidationResult(
                false,
                "{$field} must be a valid URL."
            );
        }

        // 4️⃣ Extract scheme safely
        $scheme = parse_url($value, PHP_URL_SCHEME);

        if ($scheme === null) {
            return new ValidationResult(
                false,
                "{$field} must contain a URL scheme."
            );
        }

        $scheme = strtolower($scheme);

        // 5️⃣ Validate scheme membership
        if (!in_array($scheme, $allowedSchemes, true)) {
            return new ValidationResult(
                false,
                "{$field} must use one of the following schemes: " .
                implode(', ', $allowedSchemes) . "."
            );
        }

        return new ValidationResult(true, null);
    }
}
