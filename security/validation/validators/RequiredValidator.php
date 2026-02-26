<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class RequiredValidator extends IValidator
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
                "Required rule for {$field} must be true or false."
            );
        }

        // 2️⃣ If required = false → always pass
        if ($threshold === false) {
            return new ValidationResult(true, null);
        }

        // 3️⃣ Determine presence
        $exists = $context['exists'] ?? true;

        if (!$exists || $value === null) {
            return new ValidationResult(
                false,
                "{$field} is required."
            );
        }

        return new ValidationResult(true, null);
    }
}
