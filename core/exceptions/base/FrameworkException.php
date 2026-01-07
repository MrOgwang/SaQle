<?php

namespace SaQle\Core\Exceptions\Base;

use Exception;

abstract class FrameworkException extends Exception {
     //keep contextual information
     private array $context;

     public function __construct(string $message = '', int $code = 0, array $context = []){
         $this->context = $context;

         parent::__construct($message, $code, null);
     }

     public function get_context(){
         return $this->context;
     }
}
