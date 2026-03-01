<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * Ivalidaor interface
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validation\Abstracts;

use SaQle\Security\Validation\Types\ValidationResult;

abstract class IValidator {

     protected string $field;

     protected mixed  $threshold;

     public function __construct(string $field, mixed $threshold){
         $this->field = $field;
         $this->threshold = $this->coerce_threshold($threshold);
     }

     //Coerce the threshold to the expected type
     protected function coerce_threshold(mixed $threshold): mixed {
         $type = $this->threshold_type();

         return match($type){
             'int' => $this->coerce_int($threshold),
             'float' => $this->coerce_float($threshold),
             'string' => $this->coerce_string($threshold),
             'bool' => $this->coerce_bool($threshold),
             'array' => $this->coerce_array($threshold),
             default => throw new RuntimeException("Unsupported threshold type '$type' in validator"),
         };
     }

     protected function coerce_int(mixed $value): int {
         if (is_numeric($value)) {
            return (int) $value;
         }
         throw new RuntimeException("Cannot coerce threshold to int: " . get_debug_type($value));
     }

     protected function coerce_float(mixed $value): float {
         if (is_numeric($value)) {
             return (float) $value;
         }
         throw new RuntimeException("Cannot coerce threshold to float: " . get_debug_type($value));
     }

     protected function coerce_string(mixed $value): string {
         if (is_scalar($value)) {
            return (string) $value;
         }
         throw new RuntimeException("Cannot coerce threshold to string: " . get_debug_type($value));
     }

     protected function coerce_bool(mixed $value): bool {
         if(is_string($value) && in_array(strtolower(trim($value)), ['true', 'false'])){
             return match(strtolower(trim($value))){
                'true' => true,
                'false' => false
             };
         }

         if(is_bool($value) || $value === 1 || $value === 0 || $value === '1' || $value === '0'){
             return (bool)$value;
         }

         throw new RuntimeException("Cannot coerce threshold to bool: ".get_debug_type($value));
     }

     protected function coerce_array(mixed $value): array {
         if (is_array($value)) {
            return $value;
         }

         //Coerce single values into array for choice validators
         return [$value];
     }

	 abstract public function validate(mixed $value, array $context = []): ValidationResult;
     
     public function stop_on_fail() : bool {
     	 return false;
     }

     public function message() : string {
         return "";
     }

     abstract protected function threshold_type() : string;

}
