<?php

namespace SaQle\Build\Utils;

use SaQle\Routes\{
     DeferedRoute, 
     Route, 
     Router
};
use SaQle\Core\Support\{
     Route as RouteAttribute,
     Db
};
use SaQle\Core\Registries\RouteRegistry;
use SaQle\Core\Registries\ComponentRegistry;
use ReflectionClass;
use ReflectionMethod;
use SaQle\Orm\Database\SystemSchema;
use RuntimeException;

final class RouteCompiler {

     private static function load_file_routes(){
         /**
          * Get all directories where routes live
          * 
          * 1. Top level routes in project root
          * 2. Module level routes inside module directories
          * 3. Saqle routes in saqle_routes_dirs config
          * 4. Other routes as listed in extra_routes_dirs config
          * 
          * */
         $routes_dirs = [path_join([config('base_path'), 'routes'])];

         foreach(config('app.modules', []) as $f){
             $routes_dirs[] = path_join([config('base_path'), 'modules', $f, 'routes']);
         }

         foreach(config('app.extra_routes_dirs', []) as $d){
             $routes_dirs[] = path_join([config('base_path'), $d]);
         }

         foreach(config('saqle_routes_dirs', []) as $d){
             $routes_dirs[] = $d;
         }

         foreach($routes_dirs as $dir){
             $file = path_join([$dir, "routes.php"]);
             if(file_exists($file)){
                 require_once $file;
             }
         }
     }

     private static function load_component_routes(){

         $components = ComponentRegistry::all();

         foreach($components as $component_name => $component_config){
             if($component_config['controller'] && class_exists($component_config['controller'])){

                 $class_name = $component_config['controller'];
                 $reflection = new ReflectionClass($class_name);

                 foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method){

                     //skip inherited public methods
                     if($method->getDeclaringClass()->getName() !== $class_name) {
                         continue;
                     }

                     //get route attribute
                     $route_attr = $method->getAttributes(RouteAttribute::class)[0] ?? null;

                     if($route_attr){
                         $route = $route_attr->newInstance();
                         $route->set_target($component_name."@".$method->getName());
                         $route->initialize();
                     }
                 }
             }
         }

     }

     private static function construct_route_authorization($model_label, $model_class){

         $global = trim(config('admin.authorization.global', ""));
         $route  = trim(config('admin.authorization.resources', [])[$model_label] ?? "");

         if($global && $route){
             return $global." && ".$route;
         }

         if($global && !$route){
             return $global;
         }

         return $route;
     }

     private static function construct_route_middleware($model_label, $model_class){

         $global = config('admin.middleware.global', []);
         $route  = config('admin.middleware.resources', [])[$model_label] ?? [];

         if($global && $route){
             return array_merge($global, $route);
         }

         if($global && !$route){
             return $global;
         }

         return $route;
     }

     private static function register_resource_routes($is_platform, $model_label, $model_class, $multitenancy){
         
         $authorize = $is_platform ? '__authenticated__ && __super_admin__' : 
         self::construct_route_authorization($model_label, $model_class);

         $middleware = $is_platform ? ['__authentication__', '__authorization__'] : 
         self::construct_route_middleware($model_label, $model_class);

         //list resources route
         $list_resource_route = new RouteAttribute(  
             name: admin_route_name($model_label, 'list', $is_platform),
             method: 'get', 
             url: admin_route_url($model_label, [], $is_platform),
             authorize: $authorize,
             layout: ['saqle.app'],
             model: $model_class,
             middleware: $middleware
         );
         $list_resource_route->set_target("saqle.autoresource@list_resources");
         $list_resource_route->initialize();

         //create form route
         $create_form_route = new RouteAttribute(
             name: admin_route_name($model_label, 'create.form', $is_platform),
             method: 'get', 
             url: admin_route_url($model_label, ['create'], $is_platform),
             authorize: $authorize,
             layout: ['saqle.app'],
             model: $model_class,
             middleware: $middleware
         );
         $create_form_route->set_target("saqle.autoresource@show_create_form");
         $create_form_route->initialize();

         //submit create resource route
         $create_resource_route = new RouteAttribute(
             name: admin_route_name($model_label, 'create', $is_platform),
             method: 'post', 
             url: admin_route_url($model_label, ['create'], $is_platform),
             authorize: $authorize,
             layout: ['saqle.app'],
             model: $model_class,
             middleware: $middleware
         );
         $create_resource_route->set_target("saqle.autoresource@create_resource");
         $create_resource_route->initialize();

         //show a single resource route
         $show_resource_route = new RouteAttribute(
             name: admin_route_name($model_label, 'view', $is_platform),
             method: 'get', 
             url: admin_route_url($model_label, [':id'], $is_platform),
             authorize: $authorize,
             layout: ['saqle.app'],
             model: $model_class,
             middleware: $middleware
         );
         $show_resource_route->set_target("saqle.autoresource@show_resource");
         $show_resource_route->initialize();

         //show edit resource form
         $edit_form_route = new RouteAttribute(
             name: admin_route_name($model_label, 'edit.form', $is_platform),
             method: 'get',  
             url: admin_route_url($model_label, [':id', 'edit'], $is_platform),
             authorize: $authorize,
             layout: ['saqle.app'],
             model: $model_class,
             middleware: $middleware
         );
         $edit_form_route->set_target("saqle.autoresource@show_edit_form");
         $edit_form_route->initialize();

         //edit resource route
         $edit_resource_route = new RouteAttribute(
             name: admin_route_name($model_label, 'edit', $is_platform),
             method: 'patch',  
             url: admin_route_url($model_label, [':id', 'edit'], $is_platform),
             authorize: $authorize,
             layout: ['saqle.app'],
             model: $model_class,
             middleware: $middleware
         );
         $edit_resource_route->set_target("saqle.autoresource@edit_resource");
         $edit_resource_route->initialize();

         //delete resource route
         $del_resource_route = new RouteAttribute(
             name: admin_route_name($model_label, 'delete', $is_platform),
             method: 'delete', 
             url: admin_route_url($model_label, [':id'], $is_platform),
             authorize: $authorize,
             layout: ['saqle.app'],
             model: $model_class,
             middleware: $middleware
         );
         $del_resource_route->set_target("saqle.autoresource@delete_resource");
         $del_resource_route->initialize();
     }

     private static function load_resource_routes(){
         
         $multitenancy = (bool)config('tenancy.enabled');
         $system_schema = new SystemSchema();
         $system_models = $system_schema->get_admin_models();

         foreach($system_models as $model_label => $model_class){
             self::register_resource_routes(true, $model_label, $model_class, $multitenancy);
         }

         //get developer defined db schemas
         $db_schemas = Db::get_developer_schemas();

         foreach($db_schemas as $schema_name => $schema_class){
             $models = new $schema_class()->get_admin_models();
             
             foreach($models as $model_label => $model_class){
                 self::register_resource_routes(false, $model_label, $model_class, $multitenancy);
             }
         }
     }  

     private static function load_routes(){
         //load routes from files
         self::load_file_routes();

         //load routes defined in components via Route attribute
         self::load_component_routes();

         //load automatic resource routes
         self::load_resource_routes();
     }

     public static function compile(){
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
