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

use SaQle\Routes\Router;
use SaQle\Views\View;
use SaQle\Controllers\Interfaces\WebController;
use SaQle\Commons\StringUtils;

class TargetCompiler{
     use StringUtils;

     protected array $components;

     protected string $projectroot;

     public function __construct(string $projectroot){
         $this->projectroot = $projectroot;
         $this->components = require_once $this->projectroot.CLASS_MAPPINGS_DIR."components.php";
     }

     private function compile_target($target, $action){
         $template_file = class_exists($target) ? $this->templaterefs[array_flip($this->controllerrefs)[$target]] ?? '' : $this->templaterefs[$target];

         /**
          * template file will not exist in some cases: For example when a user requests to logout.
          * */
         if(empty($template_file))
             return [[], [], [], '', '', '', []];

         $view = new View($template_file);

         $css     = $view->get_css();
         $js      = $view->get_js();
         $meta    = $view->get_meta();
         $title   = $view->get_title();
         $default = $view->get_default();
         $blocks  = $view->get_blocks();
         $block_context = [];
         $extctlers = [];

         foreach($blocks as $b){
             $block_view_path = $this->templaterefs[$b] ?? '';

             //the view file must exist for this block to be considered
             if(!$block_view_path || !file_exists($block_view_path)){
                 //in future throw an exception here!
                 continue;
             }

             $block_controller_class = $this->controllerrefs[$b] ?? '';
             if(isset($block_controller_class) && class_exists($block_controller_class)){
                 $block_controller_instance = new $block_controller_class();

                 //add to extra controllers
                 $extctlers[] = (Object)['target' => $block_controller_class, 'action' => $block_controller_instance->get_index()];
             }

             //compile the parent view with block view
             $trail = [(Object)['url' => '', 'target' => $b, 'action' => '']];
             [$block_css, $block_js, $block_meta, $block_title, $block_html] = $this->compile_trail($trail);

             $css  = array_merge($css, $block_css);
             $js   = array_merge($js, $block_js);
             $block_context[$b] = $block_html;
         }

         $html = $this->set_template_context($view->get_template(), $block_context, true);
         
         return [$css, $js, $meta, $title, $html, $default, $extctlers];
     }

     private function compile_trail(array $trail){
         $all_css   = [];
         $all_js    = [];
         $all_meta  = [];
         $all_title = "";
         $all_html  = "";
         $extra_controllers = [];

         for($t = 0; $t < count($trail); $t++){

             [$css, $js, $meta, $title, $html, $default, $extctlers] = $this->compile_target($trail[$t]->target, $trail[$t]->action);
            
             $all_css   = array_merge($all_css, $css);
             $all_js    = array_merge($all_js, $js);
             $all_meta  = array_merge($all_meta, $meta);
             $all_title = $title ? $title : $all_title;
             $all_html  = $t === 0 ? $html : preg_replace('/@content(.*?)@endcontent/', $html, $all_html);
             $extra_controllers = array_merge($extra_controllers, $extctlers);
             
             //if there is a default child
             if($t === count($trail) - 1 && $default){

                 if(class_exists($trail[$t]->target)){
                     $ctrl = $trail[$t]->target;
                     $ctrlinstance = new $ctrl();

                     if($ctrlinstance instanceof WebController){
                         $default = $ctrlinstance->get_default();
                     }
                 }

                 //at this point the default is a view name.
                 if(isset($this->controllerrefs[$default]) && class_exists($this->controllerrefs[$default])){
                     $controller_name = $this->controllerrefs[$default];
                     $controller_instance = new $controller_name();

                     //add to extra controllers
                     $extra_controllers[] = (Object)['target' => $controller_name, 'action' => $controller_instance->get_index()];
                 }

                 //add to the current trail
                 $trail[] = (Object)['url' => '', 'target' => $default, 'action' => ''];
             }
         }

         return [$all_css, $all_js, $all_meta, $all_title, $all_html, $extra_controllers];
     }

     private function get_template_name(array $trail, $index){
         if(!$trail)
             return 'unknown.html';

         $target = $trail[ count($trail) - 1]->target;

         if(!$target)
             return 'unknown.html';

         $templatename = class_exists($target) ? array_flip($this->controllerrefs)[$target] ?? '' : $target;

         //ensure this template was mapped
         if(!isset($this->templaterefs[$templatename]))
             return 'unknown.html';

         return $templatename.'_'.$index.'.html';
     }

     public function compile($changed_files){
         $route_files = array_filter($changed_files, function($file){
             $filename = basename($file['path']);
             return $file['dir'] === 'routes' && $file['type'] === 'modified' && $filename === 'web.php';
         });
         
         foreach ($route_files as $file){
             if(file_exists($file['path'])){
                 require_once $file['path'];
             }
         }

         //get all routes.
         $routes = Router::all();

         $routes_cache = [];
         $templates_cache = [];
         foreach($routes as $c => $r){
             if(!$r->redirect){
                 if(array_key_exists($r->url, $routes_cache)){
                     echo $r->url.": already exists in cache!\n";
                     continue;
                 }
                 
                 $trail = $r->get_trail();

                 [$all_css, $all_js, $all_meta, $all_title, $all_html, $extra_controllers] = $this->compile_trail($trail);

                 $page = new View($this->templaterefs['page']);
                 $compiled_template = $this->set_template_context($page->get_template(), [
                     'content' => $all_html, 
                     'title'   => $all_title, 
                     'css'     => implode("\n", array_unique($all_css)), 
                     'js'      => implode("\n", array_unique($all_js)), 
                     'meta'    => implode("\n", array_unique($all_meta))
                 ], true);

                 $templatename = $this->get_template_name($trail, $c);

                 $routes_cache[$r->url] = [
                     'trail' => $trail,
                     'extra_controllers' => $extra_controllers,
                     'template_path' => $this->projectroot.TEMPLATES_CACHE_DIR.$templatename
                 ];

                 $templates_cache[$templatename] = $compiled_template;
             }
         }

         $this->cache_routes_mapping($routes_cache);
         $this->cache_template_files($templates_cache);
     }

     private function cache_template_files($templates_cache){
         

         //write to the cache file
         $views_folder = $this->projectroot.TEMPLATES_CACHE_DIR;
         if(!file_exists($views_folder)){
             mkdir($views_folder, 0777, true);
         }

         foreach($templates_cache as $name => $content){
             $cachefile = $this->projectroot.TEMPLATES_CACHE_DIR.$name;
             file_put_contents($cachefile, $content);
         }
     }

     private function cache_routes_mapping($routes_cache){
         $export = var_export($routes_cache, true);
         $export = preg_replace('/^/m', '    ', $export); // indent

         $php =
         "<?php\n\n" .
         "return " . $export . ";\n";

         //write to the cache file
         $mappings_folder = $this->projectroot.CLASS_MAPPINGS_DIR;
         if(!file_exists($mappings_folder)){
             mkdir($mappings_folder, 0777, true);
         }

         $mappings_file = $this->projectroot.CLASS_MAPPINGS_DIR."routes.php";
         file_put_contents($mappings_file, $php);
     }
}
