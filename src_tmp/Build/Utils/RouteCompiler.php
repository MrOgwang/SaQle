<?php

namespace SaQle\Build\Utils;

use SaQle\Routing\{
     DeferedRoute, 
     Route, 
     Router,
     RouteRegistry
};
use SaQle\Http\Attributes\{
     Get,
     Post,
     Patch,
     Put,
     Delete
};
use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Core\Support\Cli;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

final class RouteCompiler {

     private static function load_file_routes(){
         /**
          * Get all directories where routes live
          * 
          * 1. Top level routes in project root
          * 2. Module level routes inside module directories
          * 3. Other routes as listed in extra_routes_dirs config
          * 
          * */
         $routes_dirs = [
             [
                 'path' => path_join([config('base_path'), 'Routes']),
                 'module' => null
             ]
         ];

         foreach(array_merge(
             config('framework_modules', []), 
             config('app.modules', []))  as $m){

             $routes_dirs[] = [
                 'path' => "",
                 'module' => $m
             ];
         }

         foreach(config('app.extra_routes_dirs', []) as $d){
             $routes_dirs[] = [
                 'path' => path_join([config('base_path'), $d]),
                 'module' => null
             ];
         }

         foreach($routes_dirs as $dir){

             if($dir['module']){

                 $module = new $dir['module']();

                 $file = path_join([$module->path('Routes'), "Routes.php"]); 

                 if(!file_exists($file)){
                     continue;
                 }
                 
                 $module_config = RouteRegistry::get_modules($module->manifest()->name);

                 Router::context($module_config)->routes(function() use ($file){
                     require_once $file;
                 });

             }else{

                 $file = path_join([$dir['path'], "Routes.php"]);

                 if(!file_exists($file)){
                     continue;
                 }

                 require_once $file;
             }
         }  
     }

     private static function load_component_routes(){

         $components = ComponentRegistry::all();

         foreach($components as $component_name => $component_config){

             //only developer created components
             if(!str_starts_with($component_name, "app") || $component_name === "app.page"){
                 continue;
             }

             Cli::print("Component: $component_name\n");

             if($component_config['controller'] && class_exists($component_config['controller'])){
        
                 $class_name = $component_config['controller'];
                 $reflection = new ReflectionClass($class_name);

                 $inspected_methods = [];

                 foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method){

                     //skip inherited public methods
                     if($method->getDeclaringClass()->getName() !== $class_name){
                         continue;
                     }

                     $method_name = $method->getName();

                     //get http method attributes
                     $http_attrs = array_merge(
                         $method->getAttributes(Get::class),
                         $method->getAttributes(Post::class),
                         $method->getAttributes(Put::class),
                         $method->getAttributes(Patch::class),
                         $method->getAttributes(Delete::class),
                     );

                     if(!empty($http_attrs)){
                         foreach($http_attrs as $attr){
                             $instance = $attr->newInstance();
                             $instance->set_target($component_name."@".$method_name);
                             $instance->initialize();
                         }
                     }else{

                         $url = "/".str_replace(".", "/", $component_name);

                         match($method_name){
                             'get'    => Router::get($url, $component_name)->name($component_name.".get"),
                             'post'   => Router::post($url, $component_name)->name($component_name.".post"),
                             'put'    => Router::put($url, $component_name)->name($component_name.".put"),
                             'patch'  => Router::patch($url, $component_name)->name($component_name.".patch"),
                             'delete' => Router::delete($url, $component_name)->name($component_name.".delete"),
                         }
                     }
                 } 

             }else{
                 /**
                  * Components without controllers will have
                  * one get route.
                  * */
                 $url = "/".str_replace(".", "/", $component_name);

                 Router::get($url, $component_name)->name($component_name);

             }
         }

     }

     private static function load_routes(){

         //load routes defined in components via Route attribute
         self::load_component_routes();

         //load routes from files
         self::load_file_routes();

     }

     public static function compile(){

         Cli::print("Compiling routes...");

         //load route files
         self::load_routes();
        
         $routes = Router::all();
         
         $compiled = [];

         foreach($routes as $r){

             $route = array_values($r)[0];

             $route_name = trim($route->name ?? $route->key);

             if($route_name){
                 if(in_array($route_name, Router::$aliases)){
                     throw new RuntimeException("Duplicate route name: {$route_name} found. Exiting!");
                 }

                 Router::$aliases[] = $route_name;
             }

             if(!$route->name){
                 $route->name($route_name);
             }

             $compiled[$route->key] = self::compile_route($route);
         }

         RouteRegistry::cache_routes_mapping($compiled, config('base_path'));

         Cli::print("Routes compiled and cached\n");
     }

     private static function get_route_variants(DeferedRoute $route) : array {

         $variants = [];

         foreach($route->routes as $name => $r){
             $variants[$name] = [
                 'name'            => $r->name,
                 'url'             => $r->url,
                 'target'          => $r->target,
                 'compiled_target' => $r->compiled_target,
                 'model_class'     => $r->model_class,
                 'guards'          => $r->guards,
                 'layout'          => $r->layout,
                 'sse_event'       => $r->sse_event,
                 'middleware'      => $r->middleware
             ];
         } 

         return $variants;
     }

     private static function compile_route(DeferedRoute | Route $route, ?Route $source = null) : array {
         $param_names = [];

         $pattern = preg_replace_callback('#:([a-zA-Z_][a-zA-Z0-9_]*)#', function ($m) use (&$param_names){
                 $param_names[] = $m[1];
                 return '([^/]+)';
             },
             $route->url
         );

         return [
             'key'         => $route->key,
             'type'        => $route instanceof Route ? 'normal' : 'conditional',
             'resolver'    => $route instanceof Route ?  null : $route->resolver,
             'method'      => $route->method,
             'pattern'     => '#^'.$pattern.'$#',
             'param_names' => $param_names,
             'route'       => $route instanceof Route ? [
                 'name'            => $route->name,
                 'url'             => $route->url,
                 'target'          => $route->target,
                 'compiled_target' => $route->compiled_target,
                 'model_class'     => $route->model_class,
                 'guards'          => $route->guards,
                 'layout'          => $route->layout,
                 'sse_event'       => $route->sse_event,
                 'middleware'      => $route->middleware
             ] : null,
             'variants'    => $route instanceof Route ? null : self::get_route_variants($route)
         ];
     }
}
