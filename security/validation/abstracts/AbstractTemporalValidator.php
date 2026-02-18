<?php

namespace SaQle\Security\Validation\Abstracts;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\ValidationResult;

abstract class AbstractTemporalValidator extends IValidator {
     
     protected string $format;

     public function __construct(string $field, mixed $threshold){
         $this->format = $threshold;

         parent::__construct($field, $threshold);
     }

     final protected function parse(mixed $value, ?string $timezone): ?DateTimeImmutable {

         if (!is_string($value) || trim($value) === '') {
             return null;
         }

         try {
            $tz = new DateTimeZone(
                $timezone ?? date_default_timezone_get()
            );
        } catch (Exception) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat(
            $this->format,
            $value,
            $tz
        );

        $errors = DateTimeImmutable::getLastErrors();

        if (
            !$date ||
            $errors['warning_count'] > 0 ||
            $errors['error_count'] > 0 ||
            $date->format($this->format) !== $value
        ) {
            return null;
        }

        return $this->normalize($date);
     }

     protected function normalize(DateTimeImmutable $date): DateTimeImmutable {
        // Normalize time-only to fixed date
        if ($this->format === 'H:i:s') {
            return $date->setDate(1970, 1, 1);
        }

        // Normalize date-only to midnight
        if ($this->format === 'Y-m-d') {
            return $date->setTime(0, 0, 0);
        }

        return $date;
     }

     final protected function failure(string $message): ValidationResult {
        return new ValidationResult(
            isvalid: false,
            message: $message
        );
     }

     final protected function success(): ValidationResult {
        return new ValidationResult(
            isvalid: true,
            message: null
        );
     }
}
