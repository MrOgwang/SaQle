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
             'int' => $this->coerceInt($threshold),
             'float' => $this->coerceFloat($threshold),
             'string' => $this->coerceString($threshold),
             'bool' => $this->coerceBool($threshold),
             'array' => $this->coerceArray($threshold),
             default => throw new RuntimeException("Unsupported threshold type '$type' in validator"),
         };

         return match ($type){
             'int'    => (int)$threshold,
             'float'  => (float)$threshold,
             'string' => (string)$threshold,
             'bool'   => is_string($threshold) && strtolower(trim($threshold)) === 'false' ? false : (bool)$threshold,
             'array'  => is_array($threshold) ? $threshold : [$threshold],
             default  => $threshold, // fallback, keep as-is
         };
     }

     protected function coerceInt(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        throw new RuntimeException("Cannot coerce threshold to int: " . get_debug_type($value));
    }

    protected function coerceFloat(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        throw new RuntimeException("Cannot coerce threshold to float: " . get_debug_type($value));
    }

    protected function coerceString(mixed $value): string
    {
        if (is_scalar($value)) {
            return (string) $value;
        }
        throw new RuntimeException("Cannot coerce threshold to string: " . get_debug_type($value));
    }

    protected function coerceBool(mixed $value): bool
    {
        if (is_bool($value) || $value === 1 || $value === 0 || $value === '1' || $value === '0') {
            return (bool) $value;
        }
        throw new RuntimeException("Cannot coerce threshold to bool: " . get_debug_type($value));
    }

    protected function coerceArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        // Coerce single values into array for choice validators
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
