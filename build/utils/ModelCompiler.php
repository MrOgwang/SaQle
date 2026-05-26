<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The ModelCompiler maps model names to fully namespaced model 
 * classes and caches the result
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

namespace SaQle\Build\Utils;

use SaQle\Orm\Entities\Model\Schema\Model;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ModelCompiler {

     use CompileUtils;

     private static function get_model_classes_from_file(string $file): array {
         $declared_before = get_declared_classes();

         include_once $file;

         $declared_after = get_declared_classes();
         $new_classes = array_diff($declared_after, $declared_before);

         $models = [];
         foreach ($new_classes as $class) {
             if (is_subclass_of($class, Model::class)) {
                $models[] = $class;
             }
         }
 
         return $models;
     }

     private static function cache_models(array $items): void {
         $caching_folder = path_join([config('base_path'), config('class_mappings_dir')]);
         if(!file_exists($caching_folder)){
             mkdir($caching_folder, 0777, true);
         }

         $caching_file = path_join([$caching_folder, "models.php"]);

         $exported_array = var_export($items, true);

         $php_content = <<<PHP
             <?php
             /**
             * This is an auto generated file: A mapping of all model names to fully namespaced
             * model classes.
             * 
             * WARNING: 
             * This file is used by the framework.
             * DO NOT MODIFY this file or its contents
             * 
             */

             return {$exported_array};
             
         PHP;

         file_put_contents($caching_file, $php_content);
     }

     public static function compile(){
         /**
          * Get all directories where models live
          * 
          * 1. Top level models in project root
          * 2. Module level models inside module directories
          * 2. Other models as listed in EXTRA_MODELS_DIRS setting
          * 
          * Module model names will be prefixed with module name
          * */
         $models_dirs = [
             [
                 'path' => path_join([config('base_path'), 'models']),
                 'prefix' => ""
             ]
         ];

         foreach(config('app.modules') as $f){
             $models_dirs[] = [
                'path' => path_join([config('base_path'), 'modules', $f, 'models']),
                'prefix' => strtolower($f)
             ];
         }

         foreach(config('app.extra_models_dirs') as $d){
             $models_dirs[] = [
                 'path' => path_join([config('base_path'), $d]),
                 'prefix' => ""
             ];
         }

         /**
          * Iterate through each models directory, mapping 
          * model name to full namespaced class name
          * */
         $models = [];

         foreach($models_dirs as $dir){

             $path = $dir['path'];
             $prefix = $dir['prefix'];

             if(!is_dir($path))
                 continue;

             $dir_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
             foreach($dir_iterator as $file){
                 if($file->isFile() && $file->getExtension() === 'php'){
                     $model_name = strtolower(str_replace(".php", "", $file->getFilename()));
                     $model_name = $prefix ? $prefix.".".$model_name : $model_name;
                     $path       = $file->getRealPath();

                     $declared_models = self::get_model_classes_from_file($path);

                     if($declared_models){
                         $models[$model_name] = $declared_models[0];
                     }
                 }
             }
         }

         self::cache_models($models);
     }
}
