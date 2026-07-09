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
     
     private static function get_model_classes_from_file(string $file) : array {
         $classes = self::get_classes_declared_in_file($file);

         //load the file if it hasn't already been loaded.
         require_once $file;

         $models = [];

         foreach($classes as $class){
             if(class_exists($class, false) && is_subclass_of($class, Model::class)){
                 $models[] = $class;
             }
         }

         return $models;
     }

     private static function get_classes_declared_in_file(string $file) : array {

         $tokens = token_get_all(file_get_contents($file));

         $namespace = '';

         $classes = [];

         $count = count($tokens);

         for($i = 0; $i < $count; $i++){
             if(!is_array($tokens[$i])){
                 continue;
             }

             if($tokens[$i][0] === T_NAMESPACE){
                 $namespace = '';

                 for($j = $i + 1; $j < $count; $j++){
                     if(!is_array($tokens[$j])){
                         if($tokens[$j] === ';' || $tokens[$j] === '{'){
                             break;
                         }

                         continue;
                     }

                     if($tokens[$j][0] === T_STRING || 
                        (defined('T_NAME_QUALIFIED') && $tokens[$j][0] === T_NAME_QUALIFIED) ||
                        (defined('T_NS_SEPARATOR') && $tokens[$j][0] === T_NS_SEPARATOR)
                     ){
                         $namespace .= $tokens[$j][1];
                     }
                 }

                 continue;
             }

             //look for classes
             if($tokens[$i][0] !== T_CLASS){
                 continue;
             }

             //ignore anonymous classes
             $previous = null;

             for($j = $i - 1; $j >= 0; $j--){
                 if(!is_array($tokens[$j])){
                     if(trim($tokens[$j]) === ''){
                         continue;
                     }

                     break;
                 }

                 if(in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])){
                     continue;
                 }

                 $previous = $tokens[$j][0];
                 break;
             }

             if($previous === T_NEW){
                 continue;
             }

             //find the class name
             for($j = $i + 1; $j < $count; $j++){
                 if(!is_array($tokens[$j])){
                     continue;
                 }

                 if($tokens[$j][0] === T_STRING){
                     $class = $tokens[$j][1];
                     $classes[] = $namespace ? $namespace . '\\' . $class : $class;

                     break;
                 }
             }
         }

         return $classes;
     }

     private static function cache_models(array $items): void {
         $caching_folder = path_join([config('base_path'), config('class_mappings_dir')]);
         if(!file_exists($caching_folder)){
             mkdir($caching_folder, 0777, true);
         }

         $caching_file = path_join([$caching_folder, "models.php"]);

         $export = var_export($items, true);
         $export = preg_replace('/^/m', '    ', $export); // indent

         $php_content ="<?php\n\n"."return ".$export.";\n";

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

         foreach(config('saqle_models_dirs') as $d){
             $models_dirs[] = [
                 'path' => $d,
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
