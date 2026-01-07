<?php

namespace SaQle\Build\Utils;

use SaQle\Routes\{Route, Router};
use SaQle\Core\Registries\RouteRegistry;

final class RouteCompiler {

     public static function compile(array $routes, string $project_root) {
         $compiled = [];

         foreach ($routes as $route){
             $compiled[] = self::compile_route($route);
         }

         RouteRegistry::cache_routes_mapping($compiled, $project_root);
     }

     private static function compile_route(Route $route, ?Route $source = null): array{
         $param_names = [];

         $pattern = preg_replace_callback('#:([a-zA-Z_][a-zA-Z0-9_]*)#', function ($m) use (&$param_names){
                 $param_names[] = $m[1];
                 return '([^/]+)';
             },
             $route->url
         );

         return [
             'method'      => $route->method,
             'pattern'     => '#^'.$pattern.'$#',
             'param_names' => $param_names,
             'route'       => [
                 'url'             => $route->url,
                 'target'          => $route->target,
                 'compiled_target' => $route->compiled_target,
                 'guards'          => $route->guards,
                 'layout'          => $route->layout,
                 'restype'         => $route->restype,
                 'trail'           => $route->trail
             ]
         ];
     }
}
