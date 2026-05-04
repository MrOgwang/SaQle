<?php

namespace SaQle\Http\Request\Execution;

use Throwable;
use ReflectionMethod;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\Message;
use SaQle\Core\Support\Allow;

final class ActionExecutor {

     public static function execute(Request $request, ?string $controller = null, ?string $method = null): Message {

         if(!$controller || !$method){
             $controller = $request->route->compiled_target->controller;
             $method = $request->route->compiled_target->method;
             if(!$controller || !$method){
                 return ok();
             }
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

             return $result instanceof Message ? $result : ok($result);

         }catch(Throwable $e){
             throw $e;
         }
     }

}
