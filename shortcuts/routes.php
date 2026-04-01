<?php

use SaQle\Routes\UrlGenerator;

if(!function_exists('route')){
     function route(string $name, array $params = []){
         return UrlGenerator::route($name, $params);
     }
}