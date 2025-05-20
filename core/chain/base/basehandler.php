<?php
namespace SaQle\Core\Chain\Base;

use SaQle\Core\Chain\Interfaces\Handler;

abstract class BaseHandler implements Handler{
     private $next_handler;
     protected $params;

     public function __construct(...$params){
     	$this->params = $params;
     }

     public function set_next(Handler $handler): Handler{
         $this->next_handler = $handler;
         return $handler;
     }

     public function handle(mixed $request): mixed{
         if ($this->next_handler){
             return $this->next_handler->handle($request);
         }

         return $request;
     }
}


