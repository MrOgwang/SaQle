<?php

namespace SaQle\Build\Utils;

use SaQle\Routes\{
     DeferedRoute, 
     Route, 
     Router
};
use SaQle\Core\Support\Route as RouteAttribute;
use SaQle\Core\Registries\RouteRegistry;
use SaQle\Core\Registries\ComponentRegistry;
use ReflectionClass;
use ReflectionMethod;

final class RouteCompiler {

     private static function filter_route_files($files){
         return array_filter($files, function($file){
             $filename = basename($file['path']);
             return $file['dir'] === 'routes' && $file['type'] === 'modified' && $filename === 'routes.php';
         });
     }

     private static function load_file_routes(array $files){
         foreach ($files as $file){
             if(file_exists($file['path'])){
                 require_once $file['path'];
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

     private static function load_resource_routes(){
         //get developer defined db schemas
         $db_schemas = config('db.schemas');

         foreach($db_schemas as $schema_name => $schema_class){
             $models = new $schema_class()->get_developer_models();

             foreach($models as $model_label => $model_class){

                 //list resources route
                 $list_resource_route = new RouteAttribute( 
                     name: $model_label.'.list',
                     method: 'get', 
                     url: '/_auto/'.$model_label, 
                     guards: 'authenticated',
                     layout: ['saqle.app'],
                     model: $model_class
                 );
                 $list_resource_route->set_target("saqle.autoresource@list_resources");
                 $list_resource_route->initialize();

                 //create form route
                 $create_form_route = new RouteAttribute(
                     name: $model_label.'.create.form',
                     method: 'get', 
                     url: '/_auto/'.$model_label.'/create', 
                     guards: 'authenticated',
                     layout: ['saqle.app'],
                     model: $model_class."@create_form"
                 );
                 $create_form_route->set_target("saqle.autoresource@show_create_form");
                 $create_form_route->initialize();

                 //submit create resource route
                 $create_resource_route = new RouteAttribute(
                     name: $model_label.'.create',
                     method: 'post', 
                     url: '/_auto/'.$model_label, 
                     guards: 'authenticated',
                     layout: ['saqle.app'],
                     model: $model_class
                 );
                 $create_resource_route->set_target("saqle.autoresource@create_resource");
                 $create_resource_route->initialize();

                 //show a single resource route
                 $show_resource_route = new RouteAttribute(
                     name: $model_label.'.view',
                     method: 'get', 
                     url: '/_auto/'.$model_label.'/:id', 
                     guards: 'authenticated',
                     layout: ['saqle.app'],
                     model: $model_class
                 );
                 $show_resource_route->set_target("saqle.autoresource@show_resource");
                 $show_resource_route->initialize();

                 //show edit resource form
                 $edit_form_route = new RouteAttribute(
                     name: $model_label.'.edit.form',
                     method: 'get', 
                     url: '/_auto/'.$model_label.'/:id/edit', 
                     guards: 'authenticated',
                     layout: ['saqle.app'],
                     model: $model_class."@update_form"
                 );
                 $edit_form_route->set_target("saqle.autoresource@show_edit_form");
                 $edit_form_route->initialize();

                 //edit resource route
                 $edit_resource_route = new RouteAttribute(
                     name: $model_label.'.edit',
                     method: 'patch', 
                     url: '/_auto/'.$model_label.'/:id', 
                     guards: 'authenticated',
                     layout: ['saqle.app'],
                     model: $model_class
                 );
                 $edit_resource_route->set_target("saqle.autoresource@edit_resource");
                 $edit_resource_route->initialize();

                 //delete resource route
                 $del_resource_route = new RouteAttribute(
                     name: $model_label.'.delete',
                     method: 'delete', 
                     url: '/_auto/'.$model_label.'/:id', 
                     guards: 'authenticated',
                     layout: ['saqle.app'],
                     model: $model_class
                 );
                 $del_resource_route->set_target("saqle.autoresource@delete_resource");
                 $del_resource_route->initialize();
             }
         }
     }

     private static function load_routes($files){
         //load routes from files
         self::load_file_routes($files);

         //load routes defined in components via Route attribute
         self::load_component_routes();

         //load automatic resource routes
         self::load_resource_routes();
     }

     public static function compile(array $modified_files){

         //filter route files
         $route_files = self::filter_route_files($modified_files);

         //load route files
         self::load_routes($route_files);
        
         $routes = Router::all();
         
         $compiled = [];

         foreach ($routes as $route){
             //$compiled[] = self::compile_route($route);
             $compiled[$route->key] = self::compile_route($route);
         }

         RouteRegistry::cache_routes_mapping($compiled, config('base_path'));
     }

     private static function get_route_variants(DeferedRoute $route) : array {

         $variants = [];

         foreach($route->routes as $name => $r){
             $variants[$name] = [
                 'name'            => $r->name,
                 'scope'           => $r->scope->value,
                 'url'             => $r->url,
                 'target'          => $r->target,
                 'compiled_target' => $r->compiled_target,
                 'model_class'     => $r->model_class,
                 'guards'          => $r->guards,
                 'layout'          => $r->layout,
                 'restype'         => $r->restype?->value,
                 'sse_event'       => $r->sse_event
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
                 'scope'           => $route->scope->value,
                 'url'             => $route->url,
                 'target'          => $route->target,
                 'compiled_target' => $route->compiled_target,
                 'model_class'     => $route->model_class,
                 'guards'          => $route->guards,
                 'layout'          => $route->layout,
                 'restype'         => $route->restype?->value,
                 'sse_event'       => $route->sse_event
             ] : null,
             'variants'    => $route instanceof Route ? null : self::get_route_variants($route)
         ];
     }
}
