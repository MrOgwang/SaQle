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
use SaQle\Orm\Entities\Model\Schema\Model;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class ClassMapper{

     protected string $projectroot;

     public function __construct(string $projectroot){
         $this->projectroot = $projectroot;
     }

     private function get_model_classes_from_file(string $file): array {
         $declaredBefore = get_declared_classes();

         // Temporarily include the file
         include_once $file;

         $declaredAfter = get_declared_classes();
         $newClasses = array_diff($declaredAfter, $declaredBefore);

         $models = [];
         foreach ($newClasses as $class) {
             if (is_subclass_of($class, Model::class)) {
                $models[] = $class;
             }
         }

         return $models;
     }

     private function cache_mappings(array $items, string $type = 'components'): void {
         //create the components mappings dir
         $mappings_folder = $this->projectroot.CLASS_MAPPINGS_DIR;
         if(!file_exists($mappings_folder)){
             mkdir($mappings_folder, 0777, true);
         }

         $mappings_file = $mappings_folder.$type.".php";

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

         foreach(SAQLE_COMPONENTS_DIRS as $d){
             $components_dirs[] = $d;
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

         $this->cache_mappings($components);
     }

     protected function map_models(){
         /**
          * Get all directories where models live
          * 
          * 1. Top level models in project root
          * 2. App level models inside app directories
          * 2. Other models as listed in EXTRA_MODELS_DIRS setting
          * */
         $models_dirs = [$this->projectroot.'/models'];

         foreach(INSTALLED_APPS as $f){
             $models_dirs[] = $this->projectroot."/apps/".$f."/models";
         }

         foreach(EXTRA_MODELS_DIRS as $d){
             $models_dirs[] = $this->projectroot."/".$d;
         }

         /**
          * Iterate through each models directory, mapping 
          * model name to full namespaced class name
          * */
         $models = [];

         foreach($models_dirs as $dir){
             $dir_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
             foreach($dir_iterator as $file){
                 if($file->isFile() && $file->getExtension() === 'php'){
                     $model_name = strtolower(str_replace(".php", "", $file->getFilename()));
                     $path       = $file->getRealPath();

                     $declared_models = $this->get_model_classes_from_file($path);

                     if($declared_models){
                         $models[$model_name] = $declared_models[0];
                     }
                 }
             }
         }

         $this->cache_mappings($models, 'models');
     }

     public function map(){
         $this->map_components();
         $this->map_models();
     }
}
