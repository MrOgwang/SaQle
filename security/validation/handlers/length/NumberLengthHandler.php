
<?php

namespace SaQle\Security\Validation\Handlers\Length;

use SaQle\Security\Validation\Interfaces\IRuleHdnadler;

class NumberLengthHandler implements IRuleHandler {

     public function validate(mixed $value, array $config): array {
         $strict_length = array_key_exists("strict_length", $config) && (bool)$config['strict_length'] === true;
         $count = $input !== 0 ? floor(log10(abs($value ?? 0)) + 1) : 1;

         return [
            'is_valid' => $strict_length ? $count === $config['length'] : $count <= $config['length'],
            'error'    => "{$config['field_label']} must be ≤ {$config['maximum']}"
         ];
     }
}
