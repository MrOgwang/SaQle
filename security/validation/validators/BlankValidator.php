<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class BlankValidator extends IValidator
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
                "Blank rule for {$field} must be true or false."
            );
        }

        // 2️⃣ Only text allowed (null included for evaluation)
        if (!is_string($value) && $value !== null) {
            return new ValidationResult(
                false,
                "{$field} must be a string."
            );
        }

        // 3️⃣ Determine blank state
        $isBlank = $value === null || trim($value) === '';

        // 4️⃣ blank = true → empty allowed → always valid
        if ($threshold === true) {
            return new ValidationResult(true, null);
        }

        // 5️⃣ blank = false → empty NOT allowed
        return !$isBlank
            ? new ValidationResult(true, null)
            : new ValidationResult(false, "{$field} cannot be blank.");
    }
}
