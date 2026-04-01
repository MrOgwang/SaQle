<?php

namespace SaQle\Core\Registries;

use InvalidArgumentException;

final class RouteRegistry {
     private static ?array $routes = null;

     public static function all(): array {
         if(self::$routes === null) {
             self::$routes = require path_join([config('base_path'), config('class_mappings_dir'), 'routes.php']);
         }

         return self::$routes;
     }

     public static function get(string $name) : ?array {
        
         $all_routes = self::all();

         foreach($all_routes as $r){
             if($r['route']['name'] === $name){
                 return $r;
             }
         }

         return null;
     }

     public static function cache_routes_mapping($routes_cache){
         $export = var_export($routes_cache, true);
         $export = preg_replace('/^/m', '    ', $export); // indent

         $php =
         "<?php\n\n" .
         "return ".$export.";\n";

         //write to the cache file
         $mappings_folder = path_join([config('base_path'), config('class_mappings_dir')]);
         if(!file_exists($mappings_folder)){
             mkdir($mappings_folder, 0777, true);
         }

         $mappings_file = path_join([$mappings_folder, "routes.php"]);
         file_put_contents($mappings_file, $php);
     }
}
