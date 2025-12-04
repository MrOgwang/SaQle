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

     private function cache_vc(array $items, string $cache_file, string $type = 'controllers'): void {
         //convert array to a PHP array representation
         $exported_array = var_export($items, true);
    
         //create the PHP file content
         $php_content = <<<PHP
             <?php
             /**
             * This is an auto generated file: This is a cache of all the $type
             * defined for your project. 
             * 
             * WARNING: Just leave this file alone kindly.
             */

             return {$exported_array};

             
         PHP;

         //write to the cache file
         $mappings_folder = $this->projectroot.CLASS_MAPPINGS_DIR;
         if(!file_exists($mappings_folder)){
             mkdir($mappings_folder, 0777, true);
         }

         file_put_contents($cache_file, $php_content);
     }

     public function map(){
         $cache_controllers_file = $this->projectroot.CLASS_MAPPINGS_DIR."controllers.php";
         $cache_views_file = $this->projectroot.CLASS_MAPPINGS_DIR."views.php";
         $controllers = [];
         $views = [];

         //get app level controllers and templates
         foreach(INSTALLED_APPS as $f){
             $controllers_folder = $this->projectroot."/apps/".$f."/controllers";
             $views_folder       = $this->projectroot."/apps/".$f."/templates";
             [$v, $c]            = $this->get_folder_vc($controllers_folder, $views_folder);
             $controllers        = array_merge($controllers, $c);
             $views              = array_merge($views, $v);
         }

         //get project level controllers and templates
         [$v, $c]     = $this->get_folder_vc($this->projectroot."/controllers", $this->projectroot."/templates");

         $controllers = array_merge($controllers, $c);
         $views       = array_merge($views, $v);

         $controllers['page'] = Page::class;
         $views['page']       = $this->projectroot."/templates/page.html";

         $this->cache_vc($controllers, $cache_controllers_file);
         $this->cache_vc($views,       $cache_views_file, 'views');
      }
}
