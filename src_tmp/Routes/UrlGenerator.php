<?php

namespace SaQle\Routes;

use SaQle\Core\Registries\RouteRegistry;
use Exception;

class UrlGenerator {
     public static function route(string $name, array $params = []): string {
         $route = RouteRegistry::get($name);

         if(!$route){
             throw new Exception("Route {$name} not found");
         }

         $url = $route['route']['url'];

         foreach($params as $key => $value){
             $url = str_replace(':'.$key, $value, $url);
         }

         return $url;
     }
}