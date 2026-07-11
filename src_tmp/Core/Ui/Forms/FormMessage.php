<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Forms;

class FormMessage {
     public function __construct(
         public string $type, // success, error, warning, info
         public string $text
     ) {}
}