<?php

namespace SaQle\Core\Exceptions\Database;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;
use Throwable;

class TableNotFoundException extends FrameworkException {

     protected string $safe_message = "Table not found!";

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
         if($context && isset($context['name']) && isset($context['tables'])){
             return "There is no table called [{$context['name']}] in the context tracker: Available tables are: ".implode(", ", $context['tables']);
         }

         return $this->safe_message;
     }
}
