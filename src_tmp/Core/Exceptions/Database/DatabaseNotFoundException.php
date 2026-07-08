<?php

namespace SaQle\Core\Exceptions\Database;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;
use Throwable;

class DatabaseNotFoundException extends FrameworkException{

     protected string $safe_message = "Database not found!";

     public function __construct(
         string $message = '', 
         array $context = [], 
         ?Throwable $prev = null
     ){
         parent::__construct(
             $message ?: $this->get_message($context), 
             FeedBack::INTERNAL_SERVER_ERROR, 
             $context, 
             $prev
         );
     }

     private function get_message(array $context){
         if($context && isset($context['name']) && isset($context['databases'])){
             return "There is no database called [{$context['name']}] in the context tracker: Available databases are: ".implode(", ", $context['databases']);
         }

         return $this->safe_message;
     }
}
