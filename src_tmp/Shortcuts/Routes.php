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

if(!function_exists('admin_route_name')){
     function admin_route_name(string $model_label, string $action = "", bool $is_platform = false){

         $route_name_prefix = $is_platform ? 'saqle' : config('admin.routes.name_prefix', "admin");

         return implode(".", [
             $route_name_prefix, 
             strtolower($model_label), 
             $action
         ]);

     }
}

if(!function_exists('admin_route_url')){
     function admin_route_url(string $model_label, array $parts = [], bool $is_platform = false){

         $model_label = strtolower($model_label);

         $url_parts = [];

         if($is_platform){
             $url_parts = array_merge(
                 ['/saqle', $model_label],
                 $parts
             );
         }else{

             $prefix = config('admin.routes.prefix', "_admin");

             $url_parts = array_merge(
                 ["/".$prefix, $model_label],
                 $parts
             );
         }

         return url_join($url_parts);

     }
}