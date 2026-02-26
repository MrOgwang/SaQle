<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class ExtensionsValidator extends IValidator
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
                "Extensions rule for {$field} must be a non-empty array."
            );
        }

        // Normalize allowed extensions (lowercase, no dot)
        $allowed = array_map(
            fn($ext) => ltrim(strtolower(trim((string)$ext)), '.'),
            $threshold
        );

        // 2️⃣ Resolve filename
        $filename = null;

        if (is_array($value) && isset($value['name'])) {
            $filename = $value['name'];
        } elseif (is_string($value)) {
            $filename = basename($value);
        }

        if (!$filename) {
            return new ValidationResult(
                false,
                "{$field} must be a valid file."
            );
        }

        // 3️⃣ Extract extension safely
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if (!$extension) {
            return new ValidationResult(
                false,
                "{$field} must have a valid file extension."
            );
        }

        $extension = strtolower($extension);

        // 4️⃣ Validate against allowed list
        if (!in_array($extension, $allowed, true)) {
            return new ValidationResult(
                false,
                "{$field} must have one of the following extensions: " 
                . implode(', ', $allowed) . "."
            );
        }

        return new ValidationResult(true, null);
    }
}
