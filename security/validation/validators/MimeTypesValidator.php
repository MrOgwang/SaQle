<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class MimeTypesValidator extends IValidator
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
                "mime_types rule for {$field} must be a non-empty array."
            );
        }

        // Normalize allowed MIME types
        $allowed = array_map(
            fn($type) => strtolower(trim((string)$type)),
            $threshold
        );

        // 2️⃣ Resolve file path
        $filePath = null;

        if (is_array($value) && isset($value['tmp_name'])) {
            $filePath = $value['tmp_name'];
        } elseif (is_string($value) && file_exists($value)) {
            $filePath = $value;
        }

        if (!$filePath || !file_exists($filePath)) {
            return new ValidationResult(
                false,
                "{$field} must be a valid file."
            );
        }

        // 3️⃣ Detect real MIME type using finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($filePath);

        if (!$detectedMime) {
            return new ValidationResult(
                false,
                "{$field} MIME type could not be determined."
            );
        }

        $detectedMime = strtolower($detectedMime);

        // 4️⃣ Validate against allowed list
        if (!in_array($detectedMime, $allowed, true)) {
            return new ValidationResult(
                false,
                "{$field} must be one of the following MIME types: "
                . implode(', ', $allowed) . "."
            );
        }

        return new ValidationResult(true, null);
    }
}
