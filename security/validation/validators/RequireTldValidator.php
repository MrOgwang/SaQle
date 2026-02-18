<?php

namespace SaQle\Security\Validation;

use SaQle\Security\Validation\Interfaces\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

class RequireTldValidator extends IValidator
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
                "require_tld rule for {$field} must be true or false."
            );
        }

        // 2️⃣ If rule is false → always pass
        if ($threshold === false) {
            return new ValidationResult(true, null);
        }

        // 3️⃣ Must be non-empty string
        if (!is_string($value) || trim($value) === '') {
            return new ValidationResult(
                false,
                "{$field} must be a valid URL."
            );
        }

        $value = trim($value);

        // 4️⃣ Validate URL structure
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return new ValidationResult(
                false,
                "{$field} must be a valid URL."
            );
        }

        // 5️⃣ Extract host
        $host = parse_url($value, PHP_URL_HOST);

        if ($host === null || $host === '') {
            return new ValidationResult(
                false,
                "{$field} must contain a valid host."
            );
        }

        // 6️⃣ Reject IP addresses (IPs do not have TLDs)
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return new ValidationResult(
                false,
                "{$field} must contain a top level domain."
            );
        }

        // 7️⃣ Check TLD existence
        // Host must contain at least one dot
        if (!str_contains($host, '.')) {
            return new ValidationResult(
                false,
                "{$field} must contain a top level domain."
            );
        }

        // Extract last label (TLD)
        $parts = explode('.', $host);
        $tld = end($parts);

        // 8️⃣ Validate TLD format (letters only, 2–63 chars per RFC)
        if (!preg_match('/^[a-z]{2,63}$/i', $tld)) {
            return new ValidationResult(
                false,
                "{$field} must contain a valid top level domain."
            );
        }

        return new ValidationResult(true, null);
    }
}
