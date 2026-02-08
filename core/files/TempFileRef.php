<?php

namespace SaQle\Core\Files;

final class TempFileRef {
     public function __construct(
         public readonly string $model,
         public readonly string $field,
         public readonly string $session,
         public readonly string $file_id
     ) {}
}
