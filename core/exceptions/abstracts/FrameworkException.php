<?php

namespace SaQle\Core\Exceptions\Abstracts;

use Exception;
use Throwable;

abstract class FrameworkException extends Exception {

     //whether to flash the message and the context to session
     private bool $do_flash = false;

     //the url to redirect to
     private string $redirect_url = "/error/500";

     //whether to log to file
     private bool $do_log = true;

     protected array $context = [];

     protected string $safe_message = "An unexpected error occurred";

     public function __construct(
         string $message = '', 
         int $code = 500, 
         array $context = [], 
         ?Throwable $previous = null
     ){
         parent::__construct($message ?: $this->safe_message, $code,  $previous);
         $this->context = $context;
     }

     public function get_context() : array {
         return $this->context;
     }

     public function get_safe_message() : string {
         return $this->safe_message;
     }

     public function flash(bool $flash = true){
         $this->do_flash = $flash;
         return $this;
     }

     public function redirect(string $url){
         $this->redirect_url = $url;
         return $this;
     }

     public function log(bool $log = true){
         $this->do_log = $log;
         return $this;
     }

     public function get_log(){
         return $this->do_log;
     }

     public function get_flash(){
         return $this->do_flash;
     }

     public function get_redirect(){
         return $this->redirect_url;
     }

     public function throw(){
         throw $this;
     }
}
