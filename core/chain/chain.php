<?php
namespace SaQle\Core\Chain;

use SaQle\Core\Chain\Interfaces\Handler;

class Chain{

     private array $handlers = [];

     public function add(Handler $handler): self{
         $this->handlers[] = $handler;
         return $this;
     }

     public function apply(mixed $request): mixed{

         foreach($this->handlers as $handler){
             $request = $handler->handle($request);
         }

         return $request;
     }

     public function is_active(){
     	return count($this->handlers) > 0 ? true : false;
     }
}

?>