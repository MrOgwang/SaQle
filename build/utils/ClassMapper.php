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
 * The ClassMapper maps controller names to fully namespaced controller classes and view names to view paths
 * and caches the result
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

namespace SaQle\Build\Utils;

use SaQle\controllers\Page;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ClassMapper{

     protected string $projectroot;

     public function __construct(string $projectroot){
         $this->projectroot = $projectroot;
     }

     private function get_folder_vc($controllers_folder, $templates_folder){
         $controllers = [];
         $views       = [];

         $controllers_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($controllers_folder));
         foreach($controllers_iterator as $file){
             if($file->isFile() && $file->getExtension() === 'php'){
                 $file_name     = str_replace(".php", "", $file->getFilename());
                 $file_path     = $file->getRealPath();

                 //read file contents
                 $content = file_get_contents($file_path);

                 //extract namespace
                 preg_match('/namespace\s+([^;]+);/', $content, $namespace_match);
                 $namespace = $namespace_match[1] ?? null;

                 //extract class name
                 preg_match('/class\s+(\w+)/', $content, $class_match);
                 $class_name = $class_match[1] ?? null;

                 if($namespace && $class_name){
                     $full_class_name = $namespace . '\\' . $class_name;
                     $controllers[$file_name] = $full_class_name;
                 }
             }
         }

         $views_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($templates_folder));
         foreach($views_iterator as $file){
             if($file->isFile() && $file->getExtension() === 'html'){
                 $file_name     = str_replace(".html", "", $file->getFilename());
                 $file_path     = $file->getRealPath();
                 $views[$file_name] = $file_path;
             }
         }

         return [$views, $controllers];
     }

     private function cache_components(array $items): void {
         //create the components mappings dir
         $mappings_folder = $this->projectroot.CLASS_MAPPINGS_DIR;
         if(!file_exists($mappings_folder)){
             mkdir($mappings_folder, 0777, true);
         }

         $mappings_file = $mappings_folder."components.php";

         //convert array to a PHP array representation
         $exported_array = var_export($items, true);
    
         //create the PHP file content
         $php_content = <<<PHP
             <?php
             /**
             * This is an auto generated file: A mapping of all component controllers and templates.
             * 
             * WARNING: This file is used by the templating engine to render your web app
             * correctly. DO NOT MODIFY the file or its contents
             */

             return {$exported_array};
             
         PHP;

         file_put_contents($mappings_file, $php_content);
     }

     protected function map_components(){
         /**
          * Get all directories where components live
          * 
          * 1. Top level components in project root
          * 2. App level components inside app directories
          * 2. Other components as listed in EXTRA_COMPONENTS_DIRS setting
          * */
         $components_dirs = [$this->projectroot.'/components'];

         foreach(INSTALLED_APPS as $f){
             $components_dirs[] = $this->projectroot."/apps/".$f."/components";
         }

         foreach(EXTRA_COMPONENTS_DIRS as $d){
             $components_dirs[] = $this->projectroot."/".$d;
         }

         /**
          * Iterate through each components directory, mapping 
          * components controllers and templates to componets names
          * */
         $components = [];
         foreach($components_dirs as $dir){
             $dir_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
             foreach($dir_iterator as $file){
                 if($file->isFile()){
                     $component_name = str_replace(".php", "", $file->getFilename());
                     $component_name = str_replace(".".COMPONENT_TEMPLATE_EXT, "", $component_name);
                     $path           = $file->getRealPath();

                     if(!isset($components[$component_name])){
                         $components[$component_name] = ['controller' => '', 'controller_path' => '', 'template_path' => ''];
                     }

                     if($file->getExtension() === COMPONENT_TEMPLATE_EXT){
                         $components[$component_name]['template_path'] = $path;
                     }elseif($file->getExtension() === 'php'){
                         $components[$component_name]['controller_path'] = $path;

                         //read file contents
                         $content = file_get_contents($path);

                         //extract namespace
                         preg_match('/namespace\s+([^;]+);/', $content, $namespace_match);
                         $namespace = $namespace_match[1] ?? null;

                         //extract class name
                         preg_match('/class\s+(\w+)/', $content, $class_match);
                         $class_name = $class_match[1] ?? null;

                         if($namespace && $class_name){
                             $components[$component_name]['controller'] = $namespace . '\\' . $class_name;
                         }
                     }
                 }
             }
         }

         $this->cache_components($components);
     }

     public function map(){
         $this->map_components();
     }
}
