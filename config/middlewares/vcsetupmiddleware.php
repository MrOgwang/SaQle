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
 * The views and controllers setup middleware
 * - These collects all
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Config\Middlewares;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SaQle\App;
use SaQle\controllers\Page;
use ReflectionClass;

class VcSetupMiddleware extends IMiddleware{
     public function handle(MiddlewareRequestInterface &$request){
         self::vc_setup();
     	 parent::handle($request);
     }

     private static function get_template_name(string $controllername){
         $parts = explode('\\', $controllername);
         return strtolower(end($parts));
     }

     private static function get_template_folder(string $controllername){
         $reflection = new ReflectionClass($controllername);
         $file_path  = $reflection->getFileName();
         $folder     = dirname($file_path);
         return str_replace("controllers", "templates", $folder);
     }

     private static function get_template_file(string $controllername){
         $template_folder = self::get_template_folder($controllername);
         $template_name   = self::get_template_name($controllername);
         $template_file   = $template_folder.DIRECTORY_SEPARATOR.$template_name.".html";

         if(!file_exists($template_file)){
             throw new \Exception("The template file: ".$template_file." does not exist!");
         }

         return $template_file;
     }

     private static function get_folder_vc($controllers_folder, $templates_folder){
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

     private static function cache_vc(array $items, string $cache_file, string $type = 'controllers'): void {
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

             ?>
         PHP;

         //write to the cache file
         if(!file_exists(DOCUMENT_ROOT."/config/setup")){
             mkdir(DOCUMENT_ROOT."/config/setup", 0777, true);
         }

         file_put_contents($cache_file, $php_content);
     }

     private static function vc_setup(){
         $app = App::init();

         $cache_controllers_file = DOCUMENT_ROOT."/config/setup/controllers.php";
         $cache_views_file       = DOCUMENT_ROOT."/config/setup/views.php";

         $controllers = [];
         $views       = [];
         if($app::getenvironment() === 'development'){
             foreach(INSTALLED_APPS as $f){
                 $controllers_folder = DOCUMENT_ROOT."/apps/".$f."/controllers";
                 $views_folder       = DOCUMENT_ROOT."/apps/".$f."/templates";
                 [$v, $c]            = self::get_folder_vc($controllers_folder, $views_folder);
                 $controllers        = array_merge($controllers, $c);
                 $views              = array_merge($views, $v);
             }

             [$v, $c]     = self::get_folder_vc(DOCUMENT_ROOT."/controllers", DOCUMENT_ROOT."/templates");
             $controllers = array_merge($controllers, $c);
             $views       = array_merge($views, $v);

             self::cache_vc($controllers, $cache_controllers_file);
             self::cache_vc($views,       $cache_views_file, 'views');
         }elseif(file_exists($cache_controllers_file) && file_exists($cache_views_file)){
             $controllers = require_once $cache_controllers_file;
             $views       = require_once $cache_views_file;
         }

         $controllers['page'] = Page::class;
         $views['page']       = DOCUMENT_ROOT."/templates/page.html";
         $app::controllers()::register($controllers);
         $app::controllers()::register($views, 'views');
     }

}
?>