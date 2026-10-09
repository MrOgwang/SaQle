<?php

namespace SaQle\Http\Request\Execution;

use Throwable;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\Message;
use SaQle\Core\Support\{
     Allow,
     ErrorComponent
};
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Core\Components\Component;
use SaQle\Core\Components\Abstracts\{
     AbstractController,
     AbstractDefinition
};
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

final class ActionExecutor {

     /**
      * Resolve the component that will be executed for the current request.
      * 
      * @param string $ref - component reference, which may be in the format component_name@method
      * or just component_name
      * 
      * @param string $http_verb - current request's http method. This will be used
      * to deduce a default controller method to execute in the case one is
      * not provided for.
      * 
      * */ 
     private static function resolve_component(string $ref, string $http_verb) : Component {

         $nameparts = explode("@", $ref);

         $component = ComponentRegistry::get_or_fail($nameparts[0], true);

         if($component->get_controller()){

             $method = $nameparts[1] ?? null;

             $component->method($method ?: self::resolve_action(
                 $component->get_controller(),
                 $http_verb,
                 [
                     self::http_method_rule(),
                     self::verb_name_rule(),
                     self::index_rule()
                 ]
             ));
         }

         return $component;

     }

     private static function resolve_action(string $class, string $http_verb, array $rules) : string {

         $reflection = new ReflectionClass($class);

         $public_methods = array_filter(
             $reflection->getMethods(ReflectionMethod::IS_PUBLIC), fn($m) => !$m->isConstructor() && !$m->isStatic()
         );

         $public_methods = array_values($public_methods);

         //Rule 1: single public method
         if(count($public_methods) === 1){
             return $public_methods[0]->getName();
         }

         foreach($rules as $rule){

             $matches = $rule($public_methods, $http_verb);

             if(count($matches) === 1){
                 return $matches[0]->getName();
             }

             if(count($matches) > 1){
                 $names = implode(', ', array_map(fn($m) => $m->getName(), $matches));
                 throw new RuntimeException("Multiple methods found while resolving action method for {$class}. Matching methods: $names!");
             }
         }

         $method_names = implode(', ', array_map(fn($m) => $m->getName(), $public_methods));
         throw new RuntimeException("No suitable method found while resolving action method for {$class}. Public methods: $method_names!");
     }

     private static function http_method_rule(): callable {
         return function(array $methods, string $verb){
             return array_values(array_filter($methods, function($method) use ($verb) {

                 $http_attrs = array_merge(
                     $method->getAttributes(Get::class),
                     $method->getAttributes(Post::class),
                     $method->getAttributes(Put::class),
                     $method->getAttributes(Patch::class),
                     $method->getAttributes(Delete::class),
                 );

                 foreach($http_attrs as $attr){
                     $instance = $attr->newInstance();
                     if($instance->method === strtoupper($verb)){
                         return true;
                     }
                 }

                 return false;
             }));
         };
     }

     private static function index_rule(): callable {
         return fn(array $methods) => array_values(array_filter($methods, fn($m) => !empty($m->getAttributes(Index::class))));
     }

     private static function verb_name_rule(): callable {
         return fn(array $methods, string $verb) => array_values(array_filter($methods, fn($m) => strtolower($m->getName()) === strtolower($verb)));
     }

     public static function execute(Request $request) : Message { 

         try{

             //resolve and initialize component
             $component = self::resolve_component($request->route->target, $request->route->method);

             //keep track of executing component
             Component::current($component);

             //initialize component
             $component->initialize($request);

             if(!$component->is_executable()){
                 return Message::ok();  
             }
             
             $controller = $component->get_controller();
             $instance = new $controller();

             $args = array_values(new ParameterResolver($request)->resolve($instance, $component->get_method()));

             $m = new ReflectionMethod($instance, $component->get_method());
             $message = $m->invokeArgs($instance, $args);

             if(!$message instanceof Message){
                 throw new RuntimeException("The action: [".$component->get_method()."] of the controller: [".$controller."] does not return a Response object!");
             }

             $component->context()->data()->replace($message->data);

             $request->target = $component;

             return $message;


             
             /*$reflection_method = new ReflectionMethod($instance, $method);

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
             }  */
 
         }catch(Throwable $e){
             throw $e;
         }
     }

}
