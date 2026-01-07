<?php

namespace SaQle\Http\Request\Execution;

use Throwable;
use ReflectionMethod;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\HttpMessage;
use SaQle\Auth\Guards\Attributes\Allow;

final class ActionExecutor {
     public function execute(Request $request, ?string $controller = null, ?string $method = null): HttpMessage {
         if(!$controller || !$method){
             return ok();
         }

         try{
             $instance = new $controller();

             $reflection_method = new ReflectionMethod($instance, $method);

             //extract allow guards
             $access_attr = $reflection_method->getAttributes(Allow::class);
             $access_control = $access_attr ? $access_attr[0]->newInstance() : null;
             if($access_control){
                 $access_control->enforce();
             }

             $resolver = new ParameterResolver($request);
             $args = $resolver->resolve($instance, $method);

             $result = $reflection_method->invokeArgs($instance, array_values($args));

             return $result instanceof HttpMessage ? $result : ok($result);

         }catch(Throwable $e){
             throw $e;
         }
     }
}
