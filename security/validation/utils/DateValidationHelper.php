<?php
namespace SaQle\Security\Validation\Utils;

use DateTimeImmutable;
use DateTimeZone;
use SaQle\Security\Validation\Types\ValidationResult;

trait DateValidationHelper {

     protected function validate_temporal(string $measure, mixed $value, array $context = []): ValidationResult {
         $timezone = $context['timezone'] ?? null;

         $value = $this->parse($value, $timezone);
         $threshold = $this->parse($this->threshold, $timezone);

         if(!$value || !$threshold){
             return $this->failure("{$this->field} is invalid.");
         }

         return $this->compare($measure, $value, $threshold) ? $this->success() : $this->failure($this->message());
     }

     protected function validate_temporal_format(mixed $value, array $context = []): ValidationResult {
         $timezone = $context['timezone'] ?? null;

         $temporal = $this->parse($value, $timezone);

         return $temporal ? $this->success() : $this->failure("{$this->field} must match the format {$this->format}.");
     }

     private function compare(string $measure, mixed $value, mixed $threshold){
         return match($measure){
             'max' => $value <= $threshold,
             'min' => $value >= $threshold
         };
     }

}
