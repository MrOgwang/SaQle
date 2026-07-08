<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

class PhoneCountryValidator extends IValidator
{
    public function validate(
        string $field,
        mixed $value,
        mixed $threshold = null, // array of allowed country codes (ISO 3166-1 alpha-2)
        array $context = []
    ): ValidationResult {

        // 1️⃣ Threshold must be non-empty array
        if (!is_array($threshold) || empty($threshold)) {
            return new ValidationResult(
                false,
                "phone_country rule for {$field} must be a non-empty array of country codes."
            );
        }

        // 2️⃣ Value must be string
        if (!is_string($value) || trim($value) === '') {
            return new ValidationResult(
                false,
                "{$field} must be a valid phone number."
            );
        }

        $value = trim($value);

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            // 3️⃣ Parse phone number (assume international format)
            $numberProto = $phoneUtil->parse($value, null);

            // 4️⃣ Check if valid number
            if (!$phoneUtil->isValidNumber($numberProto)) {
                return new ValidationResult(
                    false,
                    "{$field} is not a valid phone number."
                );
            }

            // 5️⃣ Get region (country)
            $regionCode = $phoneUtil->getRegionCodeForNumber($numberProto);

            if (!in_array($regionCode, $threshold, true)) {
                return new ValidationResult(
                    false,
                    "{$field} must belong to one of the allowed countries: " 
                    . implode(', ', $threshold) . "."
                );
            }

            // ✅ Passed
            return new ValidationResult(true, null);

        } catch (NumberParseException $e) {
            return new ValidationResult(
                false,
                "{$field} could not be parsed as a phone number: " . $e->getMessage()
            );
        }
    }
}
