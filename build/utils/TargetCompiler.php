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
use SaQle\Views\{AutoForm, View};
use SaQle\Controllers\Interfaces\WebController;
use SaQle\Commons\StringUtils;

class TargetCompiler{
     use StringUtils;

     protected array $components;

     protected array $models;

     protected string $projectroot;

     public function __construct(string $projectroot){
         $this->projectroot = $projectroot;
         $this->components = require_once $this->projectroot.CLASS_MAPPINGS_DIR."components.php";
         $this->models = require_once $this->projectroot.CLASS_MAPPINGS_DIR."models.php";
     }

     private function get_component_name(string $target, bool $throw_error = true){
         $component_name = $target;

         if(class_exists($target)){
             $target_namespaces = explode("\\", $target);
             $component_name = strtolower(end($target_namespaces));
         }

         if(!isset($this->components[$component_name])){
             if($throw_error)
                 throw new \Exception('The component for the target named: '.$target.' does not exist!\n');

             $component_name = '';
         }

         return $component_name;
     }

     private function compile_target($component_name, $action){
         $template_path = $this->components[$component_name]['template_path'];

         /**
          * template file will not exist in some cases: For example when a user requests to logout.
          * */
         if(empty($template_path))
             return [[], [], [], '', '', '', []];

         $uitree   = (Object)['component' => $component_name, 'action' => $action, 'children' => []];

         $view     = new View($template_path);
         $css      = $view->get_css();
         $js       = $view->get_js();
         $meta     = $view->get_meta();
         $title    = $view->get_title();
         $blocks   = $view->get_blocks();
         $forms    = $view->get_forms();

         print_r($forms);

         $context  = [];
         foreach($blocks as $b){
             $block_view_path = $this->components[$b]['template_path'];

             //the view file must exist for this block to be considered
             if(!$block_view_path || !file_exists($block_view_path)){
                 //in future throw an exception here!
                 continue;
             }

             $block_controller_class = $this->components[$b]['controller'];
             $block_action = '';
             if($block_controller_class && class_exists($block_controller_class)){
                 $block_controller_instance = new $block_controller_class();
                 $block_action = $block_controller_instance->get_index();
             }

             //compile the parent view with block view
             [$block_css, $block_js, $block_meta, $block_title, $block_html, $block_uitree] = $this->compile_target($b, $block_action);
             $uitree->children[] = $block_uitree;
             $css         = array_merge($css, $block_css);
             $js          = array_merge($js, $block_js);
             $wrapped     = "<!--COMPONENT:{$b}-->\n".$block_html."\n"."<!--END COMPONENT-->";
             $context[$b] = $wrapped;
         } 


         if($forms){
             $auto_form = new AutoForm();
             foreach($forms as $form){
                 //generate a form view here!
                 $form_block = $auto_form->generate_form($form, $this->models);
             }
         }

         $html = $this->set_template_context($view->get_template(), $context, true);
         
         return [$css, $js, $meta, $title, $html, $uitree];
     }

     private function compile_trail(array $trail){
         $all_css   = [];
         $all_js    = [];
         $all_meta  = [];
         $all_title = "";
         $all_html  = "";
         $ui_tree   = [];

         for($t = 0; $t < count($trail); $t++){
             $component_name = $this->get_component_name($trail[$t]->target);

             [$css, $js, $meta, $title, $html, $tree] = $this->compile_target($component_name, $trail[$t]->action);

             $ui_tree[] = $tree;
            
             $all_css   = array_merge($all_css, $css);
             $all_js    = array_merge($all_js, $js);
             $all_meta  = array_merge($all_meta, $meta);
             $all_title = $title ? $title : $all_title;

             $wrapped   = "<!--DYNAMIC:{$component_name}-->\n".$html."\n"."<!--END DYNAMIC-->";

             //$all_html  = $t === 0 ? $html : preg_replace('/@content(.*?)@endcontent/', $html, $all_html);
             $all_html  = $t === 0 ? $html : preg_replace('/@content(.*?)@endcontent/', $wrapped, $all_html);
         }

         return [$all_css, $all_js, $all_meta, $all_title, $all_html, $ui_tree];
     }

     private function get_template_name(array $trail, $index){
         if(!$trail)
             return 'unknown.html';

         $target = $trail[ count($trail) - 1]->target;

         if(!$target)
             return 'unknown.html';

         $templatename = $this->get_component_name($target, false);

         //ensure this template was mapped
         if(!$templatename)
             return 'unknown.html';

         return $templatename.'_'.$index.'.'.COMPONENT_TEMPLATE_EXT;
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

                 [$all_css, $all_js, $all_meta, $all_title, $all_html, $ui_tree] = $this->compile_trail($trail);

                 //add the root node of ui tree
                 $page_class = $this->components['page']['controller'];
                 $page_instance = new $page_class();

                 array_unshift($ui_tree, (Object)['component' => 'page', 'action' => $page_instance->get_index(), 'children' => []]);
                 //$uitree = (Object)['component' => 'page', 'action' => $page_instance->get_index(), 'children' => $ui_tree];

                 $templatename = $this->get_template_name($trail, $c);
                 $first_component_name = $this->get_component_name($trail[0]->target, false);

                 $routes_cache[$r->url] = [
                     'trail' => $trail,
                     'uitree' => $ui_tree,
                     'template_path' => $this->projectroot.TEMPLATES_CACHE_DIR.$templatename
                 ];

                 $page = new View($this->components['page']['template_path']);
                 $compiled_template = $this->set_template_context($page->get_template(), [
                     'content' => "<!--DYNAMIC:$first_component_name-->\n".$all_html."\n"."<!--END DYNAMIC-->", 
                     'title'   => $all_title, 
                     'css'     => implode("\n", array_unique($all_css)), 
                     'js'      => implode("\n", array_unique($all_js)), 
                     'meta'    => implode("\n", array_unique($all_meta))
                 ], true);

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
