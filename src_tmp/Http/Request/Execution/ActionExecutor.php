<?php

namespace SaQle\Http\Request\Execution;

use Throwable;
use ReflectionMethod;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\Message;
use SaQle\Core\Support\{
     Allow,
     ErrorComponent
};
use SaQle\Core\FeedBack\FeedBack;

final class ActionExecutor {

     public static function execute(
         Request $request, 
         ?string $controller = null, 
         ?string $method = null,
         /**
          * Props come from html component attributes.
          * Am not sure this is the right place for this, but for now,
          * we do it this way
          * */
         array $props = []
     ) : Message {

         if(!$controller || !$method){
             $controller = $request->route->compiled_target->controller;
             $method = $request->route->compiled_target->method;
             if(!$controller || !$method){
                 return Message::ok();
             }
         } 
         
         try{

             $instance = new $controller();
             $reflection_method = new ReflectionMethod($instance, $method);

             if($instance instanceof ErrorComponent){
                 
                 $params = [
                     $request->attributes->get('error.code', FeedBack::INTERNAL_SERVER_ERROR),
                     $request->attributes->get('error.message', "Internal Server Error"),
                     $request->attributes->get('error.context', null)
                 ];

                 $result = $reflection_method->invokeArgs($instance, $params);

                 return $result instanceof Message ? $result : Message::ok($result);
             }

             //extract allow guards
             $access_attr = $reflection_method->getAttributes(Allow::class);
             $access_control = $access_attr ? $access_attr[0]->newInstance() : null;
             if($access_control){
                 $access_control->enforce();
             } 

             $resolver = new ParameterResolver($request);
             $args = array_values($resolver->resolve($instance, $method, $props));
             $result = $reflection_method->invokeArgs($instance, $args);

             return $result instanceof Message ? $result : Message::ok($result);

         }catch(Throwable $e){
             throw $e;
         }
     }

}
