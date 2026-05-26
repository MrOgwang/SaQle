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
 * The component compiler maps controller names to fully 
 * namespaced controller classes and view names to view paths
 * and caches the result
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

namespace SaQle\Build\Utils;

use SaQle\Core\Support\ResolverComponent;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ComponentCompiler {

     use CompileUtils;

     private static array $asset_file_extensions = ['json', 'css', 'js'];

     private static function is_asset(string $ext){
         return in_array($ext, self::$asset_file_extensions);
     }

     public static function cache_components(array $items): void {
         
         $caching_folder = path_join([config('base_path'), config('class_mappings_dir')]);
         if(!file_exists($caching_folder)){
             mkdir($caching_folder, 0777, true);
         }

         $caching_file = path_join([$caching_folder, "components.php"]);

         $exported_array = var_export($items, true);

         $php_content = <<<PHP
             <?php
             /**
             * This is an auto generated file: A mapping of all component controllers and templates.
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

         $components_dirs = [];

         /**
          * Framework components
          * They are prefixed with the name saqle.
          * 
          * IMPORTANT
          * Framework components are listed first here to provide
          * the developer an easy way to override them when needed
          * */
         foreach(config('saqle_components_dirs') as $d){
             $components_dirs[] = [
                 'path' => $d, 
                 'prefix' => 'saqle'
             ];
         }

         /**
          * Project level components
          * They have no prefix to their names
          * */
         $components_dirs[] = [ 
             'path' => path_join([config('base_path'), 'components']),
             'prefix' => ''
         ];

         /**
          * Module components
          * Module component names are prefixed with module name
          * */
         foreach(config('app.modules') as $f){
             $components_dirs[] = [
                 'path' => path_join([config('base_path'), 'modules', $f, 'components']),
                 'prefix' => strtolower($f)
             ];
         }

         /**
          * The developer may define components in folders
          * not standard to the framework. In this case, those 
          * directories will be listed in extra_components_dirs
          * so that they can be compiled as well.
          * 
          * These components have no prefix to their names and are treated
          * as project level components
          * */
         foreach(config('app.extra_components_dirs') as $d){
             $components_dirs[] = [
                 'path' => path_join([config('base_path'), $d]),
                 'prefix' => ''
             ];
         }

         /**
          * Iterate through each components directory, mapping 
          * components controllers and templates to componets names
          * */
         $components = [];

         foreach($components_dirs as $dir){

             $path = $dir['path'];
             $prefix = $dir['prefix'];

             if(!is_dir($path)){
                 continue;
             }
             
             $dir_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
             foreach($dir_iterator as $file){
                 if($file->isFile()){

                     $extension = $file->getExtension();

                     if(self::is_asset($extension)){
                         continue;
                     }

                     /**
                      * The component name comes from the actual file name(php or html)
                      * prefixed with the relevant prefix
                      * */
                     $component_name = str_replace(".php", "", $file->getFilename());
                     $component_name = str_replace(".".config('app.component_template_ext'), "", $component_name);
                     $component_name = $prefix ? $prefix.".".$component_name : $component_name;

                     $real_path      = $file->getRealPath();
                     [$compile_path, $owner] = self::normalize_path($real_path);

                     if(!isset($components[$component_name])){
                         $components[$component_name] = [
                             'controller' => '', 
                             'controller_path' => '', 
                             'template_path' => '', 
                             'owner' => $owner,
                             'proxy' => false
                         ];
                     }

                     if($file->getExtension() === config('app.component_template_ext')){
                         $components[$component_name]['template_path'] = $compile_path;
                     }elseif($file->getExtension() === 'php'){
                         $components[$component_name]['controller_path'] = $compile_path;

                         //read file contents
                         $content = file_get_contents($real_path);

                         //extract namespace
                         preg_match('/namespace\s+([^;]+);/', $content, $namespace_match);
                         $namespace = $namespace_match[1] ?? null;

                         //extract class name
                         preg_match('/class\s+(\w+)/', $content, $class_match);
                         $class_name = $class_match[1] ?? null;

                         if($namespace && $class_name){
                             $namespaced_class_name = $namespace.'\\'.$class_name;

                             if(is_a($namespaced_class_name, ResolverComponent::class, true)){
                                 $components[$component_name]['proxy'] = true;
                             }

                             $components[$component_name]['controller'] = $namespaced_class_name;
                         }
                     }
                 }
             }
         }

         self::cache_components($components);
     }
}
