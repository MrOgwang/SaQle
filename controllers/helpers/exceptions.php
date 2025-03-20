<?php
namespace SaQle\Controllers\Helpers;

#[Attribute(Attribute::TARGET_METHOD)]
class Exceptions {
     public array $exceptions;

     public function __construct(array $exceptions = []) {
         $this->exceptions = $exceptions;
     }
}
?>
