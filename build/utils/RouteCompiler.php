<?php

namespace SaQle\Build\Utils;

use SaQle\Routes\{DeferedRoute, Route, Router};
use SaQle\Core\Registries\RouteRegistry;

final class RouteCompiler {

     public static function compile(){
        
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
