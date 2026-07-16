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