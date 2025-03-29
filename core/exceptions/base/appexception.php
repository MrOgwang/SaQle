<?php

namespace SaQle\Core\Exceptions\Base;

use Exception;

abstract class AppException extends Exception{
     private mixed $data;

     public function __construct($message, $code = 0, $data = null){
         $this->data = $data;
         parent::__construct($message, $code, null);
     }

     public function getData(){
         return $this->data;
     }
}
?>