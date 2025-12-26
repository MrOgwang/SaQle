<?php

namespace SaQle\Build\Utils;

use SaQle\Routes\{Route, Router};

final class RouteCompiler {

     public static function compile(array $routes, string $project_root) {
         $compiled = [];

         foreach ($routes as $route){
             $compiled[] = self::compile_route($route);
         }

         self::cache_routes_mapping($compiled, $project_root);
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
             ]
         ];
     }

     private static function cache_routes_mapping($routes_cache, $project_root){
         $export = var_export($routes_cache, true);
         $export = preg_replace('/^/m', '    ', $export); // indent

         $php =
         "<?php\n\n" .
         "return ".$export.";\n";

         //write to the cache file
         $mappings_folder = $project_root.CLASS_MAPPINGS_DIR;
         if(!file_exists($mappings_folder)){
             mkdir($mappings_folder, 0777, true);
         }

         $mappings_file = $project_root.CLASS_MAPPINGS_DIR."routes1.php";
         file_put_contents($mappings_file, $php);
     }
}
