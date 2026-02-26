<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class NativeTypeValidator extends IValidator
{
    private const SUPPORTED_TYPES = [
        'integer',
        'string',
        'float',
        'file'
    ];

    public function validate(
        string $field,
        mixed $value,
        mixed $threshold = null,
        array $context = []
    ): ValidationResult {

        // 1️⃣ Threshold must be valid type
        if (!is_string($threshold) || !in_array($threshold, self::SUPPORTED_TYPES, true)) {
            return new ValidationResult(
                false,
                "Invalid native_type rule for {$field}."
            );
        }

        return match ($threshold) {

            'integer' => $this->validateInteger($field, $value),
            'string'  => $this->validateString($field, $value),
            'float'   => $this->validateFloat($field, $value),
            'file'    => $this->validateFile($field, $value, $context),

            default   => new ValidationResult(false, "Unsupported native type.")
        };
    }

    private function validateInteger(string $field, mixed $value): ValidationResult
    {
        return is_int($value)
            ? new ValidationResult(true, null)
            : new ValidationResult(false, "{$field} must be an integer.");
    }

    private function validateString(string $field, mixed $value): ValidationResult
    {
        return is_string($value)
            ? new ValidationResult(true, null)
            : new ValidationResult(false, "{$field} must be a string.");
    }

    private function validateFloat(string $field, mixed $value): ValidationResult
    {
        return is_float($value)
            ? new ValidationResult(true, null)
            : new ValidationResult(false, "{$field} must be a float.");
    }

    private function validateFile(
        string $field,
        mixed $value,
        array $context
    ): ValidationResult {

        if (!is_array($value)) {
            return new ValidationResult(false, "{$field} must be a valid uploaded file.");
        }

        $requiredKeys = ['name', 'type', 'tmp_name', 'error', 'size'];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $value)) {
                return new ValidationResult(false, "{$field} is not a valid file structure.");
            }
        }

        if ($value['error'] !== UPLOAD_ERR_OK) {
            return new ValidationResult(false, "{$field} upload failed.");
        }

        $skipUploadCheck = $context['skip_upload_check'] ?? false;

        if (!$skipUploadCheck && !is_uploaded_file($value['tmp_name'])) {
            return new ValidationResult(false, "{$field} is not a valid uploaded file.");
        }

        return new ValidationResult(true, null);
    }
}
