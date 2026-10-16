<?php

namespace SaQle\Core\Exceptions\Abstracts;

use SaQle\Http\Message\Message;
use SaQle\Core\Exceptions\ServerException;
use Exception;
use Throwable;
use Closure;

abstract class AppException extends Exception {

     //array of key => value context data
     protected array $context = [];

     //front facing safe message
     protected string $safe_message = "An unexpected error occurred";

     public function __construct(string $message = '', int $code = 500, array $context = [], ?Throwable $previous = null){

         $this->safe_message = $message ?: $this->safe_message;

         $this->context = $context;
         
         parent::__construct(code: $code,  previous: $previous);
     }

     public function with(string $key, mixed $value): static {

         $this->context[$key] = $value;

         return $this;
     }

     public function getContext() : array {
         return $this->context;
     }

     public function getSafeMessage() : string {
         return $this->safe_message;
     }

     public function throw(?Closure $response = null){

         if($response){
             $this->http_message = $response($this->http_message);

             if(!$this->http_message instanceof Message){
                 throw new ServerException("The response callback to ".$this::class.":throw() does not return a Message instance!");
             }
         }

         throw $this;
     }
}
