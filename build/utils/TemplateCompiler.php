<?php

namespace SaQle\Build\Utils;

use SaQle\Core\Registries\RouteRegistry;
use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Commons\StringUtils;
use SaQle\Views\{AutoForm, View};
use RuntimeException;

final class TemplateCompiler {
     use StringUtils;

     private static function compile_target($resolved_component){
         $component_name = $resolved_component[0];
         $template_path = $resolved_component[3];

         /**
          * template file will not exist in some cases: For example when a user requests to logout.
          * */
         if(empty($template_path))
             return [[], [], [], '', ''];

         $view     = new View($template_path);
         $css      = $view->get_css();
         $js       = $view->get_js();
         $meta     = $view->get_meta();
         $title    = $view->get_title();
         $blocks   = $view->get_blocks();
         $forms    = $view->get_forms();

         $context  = [];
         foreach($blocks as $b){
             $resolved_block = ComponentRegistry::resolve_component($b, 'GET', 'layout');
             $block_view_path = $resolved_block[3];

             //the view file must exist for this block to be considered
             if(!$block_view_path || !file_exists($block_view_path)){
                 throw new RuntimeException("The template file: '$block_view_path' does not exist!");
             }

             //compile the parent view with block view
             [$block_css, $block_js, $block_meta, $block_title, $block_html] = self::compile_target($resolved_block);
             $css         = array_merge($css, $block_css);
             $js          = array_merge($js, $block_js);
             $wrapped     = "<!--COMPONENT:{$b}-->\n".$block_html."\n"."<!--END COMPONENT-->";
             $context[$b] = $wrapped;
         } 

         if($forms){
             $auto_form = new AutoForm();
             foreach($forms as $form){
                 //generate a form view here!
                 //$form_block = $auto_form->generate_form($form, $this->models);
             }
         }

         $html = self::set_template_context($view->get_template(), $context, true);
         
         return [$css, $js, $meta, $title, $html];
     }

     private static function compile_trail(array $trail){
         $all_css   = [];
         $all_js    = [];
         $all_meta  = "";
         $all_title = "";
         $all_html  = "";

         for($t = 0; $t < count($trail); $t++){
             $component_name = $trail[$t][0];

             [$css, $js, $meta, $title, $html] = self::compile_target($trail[$t]);

             $all_css   = array_merge($all_css, $css);
             $all_js    = array_merge($all_js, $js);
             $all_meta  = $meta ? $meta : $all_meta;
             $all_title = $title ? $title : $all_title;

             $wrapped   = "<!--DYNAMIC:{$component_name}-->\n".$html."\n"."<!--END DYNAMIC-->";

             $all_html  = $t === 0 ? $html : preg_replace('/@content(.*?)@endcontent/', $wrapped, $all_html);
         }

         return [$all_css, $all_js, $all_meta, $all_title, $all_html];
     }

     public static function compile(string $project_root) {

         $routes = RouteRegistry::all();
         $new_routes = [];

         $templates_cache = [];
         foreach($routes as $c => $r){
             $r['route']['templates'] = [];

             if(!in_array('html', $r['route']['restype'])){
                 echo $r['route']['url'].": doesnt need a template!\n";
                 continue;
             }
             
             $whole_trail = $r['route']['trail'];

             foreach($whole_trail as $templatename => $trail){
                 $page_target = array_shift($trail);
                 [$all_css, $all_js, $all_meta, $all_title, $all_html] = self::compile_trail($trail);

                 $page = new View($page_target[3]);

                 //print_r($trail[0]);
                 $compiled_template = self::set_template_context($page->get_template(), [
                     'content' => "<!--DYNAMIC:".$trail[0][0]."-->\n".$all_html."\n"."<!--END DYNAMIC-->", 
                     'title'   => $all_title, 
                     'css'     => implode("\n", array_unique($all_css)), 
                     'js'      => implode("\n", array_unique($all_js)), 
                     'meta'    => $all_meta
                 ], true);

                 $filename = $templatename.'.'.config('component_template_ext');
                 $r['route']['templates'][$templatename] = $project_root.config('templates_cache_dir').$filename;
                 $templates_cache[$filename] = $compiled_template;
             }

             $new_routes[] = $r;
         }

         self::cache_template_files($project_root, $templates_cache);
         RouteRegistry::cache_routes_mapping($new_routes, $project_root);
     }

     private static function cache_template_files(string $project_root, $templates_cache){
         //write to the cache file
         $views_folder = $project_root.config('templates_cache_dir');
         if(!file_exists($views_folder)){
             mkdir($views_folder, 0777, true);
         }

         foreach($templates_cache as $name => $content){
             $cachefile = $project_root.config('templates_cache_dir').$name;
             file_put_contents($cachefile, $content);
         }
     }
}
