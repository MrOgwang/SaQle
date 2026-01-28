
<?php

namespace SaQle\Security\Validation\Handlers\Max;

use SaQle\Security\Validation\Interfaces\IRuleHdnadler;

class TextMaxHandler implements IRuleHandler {

     public function validate(mixed $value, array $config): array {
         $length = mb_strlen($value ?? '');
         $inclusive = $config['max_inclusive'] ?? true;

         return [
            'is_valid' => $inclusive ? $length <= $config['maximum'] : $length <  $config['maximum'],
            'error' => "{$config['field_label']} must not exceed {$config['maximum']} characters"
         ];
     }
}
