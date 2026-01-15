<?php
namespace SaQle\Build\Utils;

use ReflectionClass;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SaQle\Orm\Entities\Model\Schema\Model;

final class ResourceRouteGenerator {

     public static function execute(string $projectroot){

         $model_dirs = [$projectroot.'/models'];
         foreach(config('installed_apps') as $f){
             $model_dirs[] = $projectroot."/apps/".$f."/models";
         }
         
         $generated_models = [];
         foreach($model_dirs as $dir){
             $models = self::generate($dir, $generated_models);

             $generated_models = array_merge($generated_models, $models);
         }
     }

     public static function generate(string $dir, array $processed_models){
         $models = array_values(array_diff(self::discover_models($dir), $processed_models));
         $routes_dir = str_replace('models', 'routes', $dir);
         $file   = self::next_file($routes_dir);
         
         $lines = [
            "<?php",
            "declare(strict_types=1);",
            "",
            "use SaQle\\Routes\\Router;",
            "",
            "/**",
            " * AUTO-GENERATED JSON:API RESOURCE ROUTES",
            " * Safe to edit. Will not be overwritten.",
            " */",
         ];

         foreach ($models as $model) {
             $lines[] = "use {$model};";
         }

         $lines[] = "";

         foreach ($models as $model) {
            $ref      = new ReflectionClass($model);
            $short    = $ref->getShortName();
            $resource = self::resource_name($short);

            $lines[] = "/*";
            $lines[] = "|--------------------------------------------------------------------------";
            $lines[] = "| {$short}";
            $lines[] = "|--------------------------------------------------------------------------";
            $lines[] = "*/";
            $lines[] = "";

            $lines[] = "Router::match(['GET','POST'], '/{$resource}/', 'resource', {$short}::class);";
            $lines[] = "Router::match(['GET','PATCH','DELETE'], '/{$resource}/:id/', 'resource', {$short}::class);";
            $lines[] = "Router::match(['POST','PATCH','DELETE'], '/{$resource}/bulk/', 'resource', {$short}::class);";

            $lines[] = "Router::get('/{$resource}/:id/relationships/:rel/', 'resource', {$short}::class);";
            $lines[] = "Router::get('/{$resource}/:id/:rel/', 'resource', {$short}::class);";

            $lines[] = "";
         }

         file_put_contents($file, implode("\n", $lines));

         return $models;
     }

     /**
     * @return array<class-string<Model>>
     */
     private static function discover_models(string $dir): array {
         $before = get_declared_classes();

         foreach (glob($dir . '/*.php') as $file) {
             require_once $file;
         }

         $after = get_declared_classes();

         $newClasses = array_diff($after, $before);

         $dir = realpath($dir);

         return array_values(array_filter(
             $newClasses,
             function (string $class) use ($dir) {
                 if (!is_subclass_of($class, Model::class)) {
                     return false;
                 }

                 $ref = new \ReflectionClass($class);

                 // must be concrete
                 if ($ref->isAbstract()) {
                     return false;
                 }

                 // must originate from THIS directory
                 $file = $ref->getFileName();
                 if ($file === false) {
                     return false;
                 }

                 return str_starts_with(realpath($file), $dir);
             }
         ));
     }

     private static function resource_name(string $class): string {
         return strtolower(
            preg_replace('/(?<!^)[A-Z]/', '-$0', $class)
         ) . 's';
     }

     private static function next_file(string $dir): string {
         return $dir.'/resources.php';

         /*if(!is_dir($dir)){
             mkdir($dir, 0777, true);
         }

         $files = glob($dir.'/resources*.php');
         if(!$files){
             return $dir.'/resources.php';
         }

         $indexes = array_map(
             fn($f) => (int) preg_replace('/\D/', '', basename($f)),
             $files
         );

         return $dir.'/resources'.(max($indexes) + 1).'.php';*/
     }
     
}
