<?php

namespace SaQle\Core\Exceptions\Abstracts;

use SaQle\Http\Response\HttpMessage;
use SaQle\Core\Exceptions\ServerException;
use Exception;
use Throwable;
use Closure;

abstract class FrameworkException extends Exception {

     //customize how to respond when this exception happens
     protected ?HttpMessage $http_message = null;

     //array of key => value context data
     protected array $context = [];

     //front facing safe message
     protected string $safe_message = "An unexpected error occurred";

     public function __construct(
         string $message = '', 
         int $code = 500, 
         array $context = [], 
         ?Throwable $previous = null
     ){
         parent::__construct($message ?: $this->safe_message, $code,  $previous);
         
         $this->context = $context;

         $this->initilialize_http_message($this->getCode(), $this->get_context(), $this->getMessage());
     }

     private function initilialize_http_message(int $code, array $context, string $message){
         $this->http_message = new HttpMessage($code, $context, $message);

         //set default redirect url, flash and log flags
         $this->http_message->redirect(route('app.error', ['code' => $code]));

         $this->http_message->log(false);

         $this->http_message->flash(false);
     }

     public function get_context() : array {
         return $this->context;
     }

     public function get_safe_message() : string {
         return $this->safe_message;
     }

     public function throw(?Closure $response = null){

         if($response){
             $this->http_message = $response($this->http_message);

             if(!$this->http_message instanceof HttpMessage){
                 throw new ServerException("The response callback to ".$this::class.":throw() does not return a response!");
             }
         }

         throw $this;
     }

     public function get_http_message(){
         return $this->http_message;
     }
}
