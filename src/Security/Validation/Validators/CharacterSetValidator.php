<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class CharacterSetValidator extends IValidator {
    public function validate(
        mixed $value,
        mixed $threshold = null,
        array $context = []
    ): ValidationResult {

        $nullable   = $context['nullable'] ?? false;
        $encoding   = $context['encoding'] ?? null;
        $asciiOnly  = $context['ascii_only'] ?? false;
        $unicodeSet = $context['unicode_script'] ?? null;
        $message    = $context['message'] ?? "{$field} contains invalid characters.";

        if ($value === null || $value === '') {
            return $nullable
                ? new ValidationResult(true, null)
                : new ValidationResult(false, "{$field} cannot be empty.");
        }

        if (!is_string($value)) {
            return new ValidationResult(false, "{$field} must be a string.");
        }

        // 1️⃣ Validate encoding if specified
        if ($encoding !== null) {
            if (!mb_check_encoding($value, $encoding)) {
                return new ValidationResult(
                    false,
                    "{$field} must be valid {$encoding} encoded text."
                );
            }
        }

        // 2️⃣ ASCII only
        if ($asciiOnly) {
            if (!preg_match('/^[\x00-\x7F]*$/', $value)) {
                return new ValidationResult(
                    false,
                    "{$field} must contain ASCII characters only."
                );
            }
        }

        // 3️⃣ Unicode script validation (Latin, Arabic, Cyrillic, etc.)
        if ($unicodeSet !== null) {
            $pattern = '/^\p{' . $unicodeSet . '}+$/u';

            if (!@preg_match($pattern, $value)) {
                return new ValidationResult(
                    false,
                    "Invalid Unicode script specified."
                );
            }

            if (!preg_match($pattern, $value)) {
                return new ValidationResult(
                    false,
                    "{$field} must contain only {$unicodeSet} characters."
                );
            }
        }

        // 4️⃣ Allowed character pattern (threshold)
        if (is_string($threshold) && trim($threshold) !== '') {

            $pattern = $this->normalizePattern($threshold);

            $result = @preg_match($pattern, $value);

            if ($result === false || preg_last_error() !== PREG_NO_ERROR) {
                return new ValidationResult(
                    false,
                    "Invalid character set pattern supplied."
                );
            }

            if ($result !== 1) {
                return new ValidationResult(false, $message);
            }
        }

        return new ValidationResult(true, null);
    }

    private function normalizePattern(string $pattern): string
    {
        $pattern = trim($pattern);

        // If already wrapped
        if (preg_match('/^(.).+\\1[imsxuADSUXJu]*$/', $pattern)) {
            return $pattern;
        }

        return '/' . str_replace('/', '\/', $pattern) . '/u';
    }
}
