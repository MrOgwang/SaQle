
<?php

namespace SaQle\Security\Validation\Handlers\Max;

use SaQle\Security\Validation\Interfaces\IRuleHdnadler;

class NumberMaxHandler implements IRuleHandler {

     public function validate(mixed $value, array $config): array {
         $inclusive = $config['max_inclusive'] ?? true;

         return [
            'is_valid' => $inclusive ? $value <= $config['maximum'] : $value <  $config['maximum'],
            'error'    => "{$config['field_label']} must be ≤ {$config['maximum']}"
         ];
     }
}
