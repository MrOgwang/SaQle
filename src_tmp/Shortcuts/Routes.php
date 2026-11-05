<?php

use SaQle\Commons\Url;
use SaQle\Routing\UrlGenerator;
use SaQle\Core\Registries\TableRegistry;
use SaQle\Auth\Context\ActorContext;
use SaQle\Http\Kernel\RouteMatcher;

if(!function_exists('route')){
     function route(string $name, array $params = [], array $queries = []){
         $url = UrlGenerator::route($name, $params);

         foreach($queries as $q => $v){
             $url = add_query_param($url, $q, $v);
         }

         return $url;

     }
}

if(!function_exists('add_query_param')){
     function add_query_param($url, $param_name, $param_value){
         return Url::add_query($url, $param_name, $param_value);
     }
}

if(!function_exists('resource_route_name')){
     function resource_route_name(string $action, ?string $model = null, ?bool $is_platform = null){

         $is_platform ??= ActorContext::is_platform();

         $prefix = $is_platform ? 'saqle' : trim(config('admin.routes.name_prefix', "admin"));

         if($model){

             $table = TableRegistry::get_model_table($model);

             return implode(".", [$prefix, $table, $action]);
         }

         return implode(".", [$prefix, $action]);

     }
}

//given a uri and a method, return the route name
if(!function_exists('route_name')){
     function route_name(string $uri, ?string $method = null) : string {

         $route = RouteMatcher::match($uri, $method);

         if(!$route){
             return "";
         }

         return $route->name;

     }
}

//given a route name, check if current route matches.
if(!function_exists('is_route')){
     function is_route(string $name) : bool {

         $request = request();

         $route = RouteMatcher::match($request->uri(), $request->method());

         if(!$route){
             return false;
         }

         return $route->name === $name;

     }
}