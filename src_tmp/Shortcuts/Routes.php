<?php

use SaQle\Commons\Url;
use SaQle\Routes\UrlGenerator;

if(!function_exists('route')){
     function route(string $name, array $params = []){
         return UrlGenerator::route($name, $params);
     }
}

if(!function_exists('add_query_param')){
     function add_query_param($url, $param_name, $param_value){
         return Url::add_query($url, $param_name, $param_value);
     }
}

/**
 * Return resource route name
 * */
if(!function_exists('rr_name')){
     function rr_name($model_label, $action){

         $route_name_prefix = config('admin.routes.name_prefix', "admin");

         return implode(".", [
             $route_name_prefix, 
             $model_label, 
             $action
         ]);
     }
}

if(!function_exists('admin_route_name')){
     function admin_route_name(string $model_label, string $action = ""){

         $route_name_prefix = config('admin.routes.name_prefix', "admin");

         return implode(".", [
             $route_name_prefix, 
             $model_label, 
             $action
         ]);

     }
}