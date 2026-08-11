<?php

use SaQle\Commons\Url;
use SaQle\Routing\UrlGenerator;
use SaQle\Core\Registries\TableRegistry;
use SaQle\Auth\Context\ActorContext;

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