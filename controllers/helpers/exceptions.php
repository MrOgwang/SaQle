<?php
namespace SaQle\Controllers\Helpers;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Exceptions {
     public array $exceptions;

     public function __construct(array $exceptions = []) {
         $this->exceptions = $exceptions;
     }
}
?>
