<?php

namespace SaQle\Core\Exceptions\Base;

use Exception;

abstract class AppException extends Exception{
     private mixed $data;

     private string $redirect = '';

     public function __construct(string $message, int $code = 0, array $data = [], string $redirect = ''){
         $this->data = $data;
         $this->redirect = $redirect;
         parent::__construct($message, $code, null);
     }

     public function getData(){
         return $this->data;
     }

     public function getRedirect(){
         return $this->redirect;
     }
}
?>