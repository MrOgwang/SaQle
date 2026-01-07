<?php

namespace SaQle\Core\Registries;

use InvalidArgumentException;

final class RouteRegistry {
     private static ?array $routes = null;

     public static function all(): array{
         if (self::$routes === null) {
             self::$routes = require DOCUMENT_ROOT.CLASS_MAPPINGS_DIR.'routes.php';
         }

         return self::$routes;
     }

     public static function cache_routes_mapping($routes_cache, $project_root){
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

         $mappings_file = $project_root.CLASS_MAPPINGS_DIR."routes.php";
         file_put_contents($mappings_file, $php);
     }
}
