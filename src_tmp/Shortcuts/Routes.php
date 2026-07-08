<?php

use SaQle\Commons\UrlUtils;
use SaQle\Routes\UrlGenerator;

if(!function_exists('route')){
     function route(string $name, array $params = []){
         return UrlGenerator::route($name, $params);
     }
}

if(!function_exists('add_query_param')){
     function add_query_param($url, $param_name, $param_value){
         return (new class { use UrlUtils; })::add_url_parameter($url, $param_name, $param_value);
     }
}