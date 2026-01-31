<?php

namespace SaQle\Security\Validation\Handlers\Max;

use SaQle\Security\Validation\Interfaces\IRuleHandler;

class FileMaxHandler implements IRuleHandler {

     public function validate(mixed $value, array $config): array {
         $max_bytes = $config['maximum'] * 1024 * 1024;
         $inclusive = $config['max_inclusive'] ?? true;

         return [
             'is_valid' => $inclusive ? $value['size'] <= $max_bytes : $value['size'] <  $max_bytes,
             'error' => "{$config['field_label']} file size exceeds {$config['maximum']}MB"
         ];
     }
}
