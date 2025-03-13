<?php
namespace SaQle\Http\Request\Processors;

use SaQle\Controllers\Page;
use ReflectionClass;
use SaQle\Views\View;
use SaQle\Templates\Static\AppStatic;
use SaQle\Templates\Meta\AppMeta;
use SaQle\Controllers\Refs\ControllerRef;
use SaQle\Templates\Context\AppContext;
use SaQle\Commons\StringUtils;
use SaQle\Http\Request\Middleware\CsrfMiddleware;

class WebRequestProcessor extends RequestProcessor{
	 use StringUtils;

     private $controllerrefs = [];
     private $templaterefs   = [];

     public function __construct(){
     	 $this->controllerrefs = ControllerRef::init()::get_controllers();
     	 $this->templaterefs = ControllerRef::init()::get_views();
     	 parent::__construct();
     }

	 private function get_template_name(string $controllername){
         $parts = explode('\\', $controllername);
         return strtolower(end($parts));
	 }

	 private function get_template_folder(string $controllername){
	 	 $reflection = new ReflectionClass($controllername);
	     $file_path  = $reflection->getFileName();
	     $folder     = dirname($file_path);
	     return str_replace("controllers", "templates", $folder);
	 }

	 private function get_template_file(string $controllername){
	     $template_folder = $this->get_template_folder($controllername);
	     $template_name   = $this->get_template_name($controllername);
	     $template_file   = $template_folder.DIRECTORY_SEPARATOR.$template_name.".html";

	     if(!file_exists($template_file)){
	     	 throw new \Exception("The template file: ".$template_file." does not exist!");
	     }

	     return $template_file;
	 }

     private function process_trail(array $trail){
     	 $all_css   = [];
         $all_js    = [];
         $all_meta  = [];
         $all_title = "";
         $all_html  = "";

         $parent_context = [];
         for($t = 0; $t < count($trail); $t++){
         	 [$css, $js, $meta, $title, $html, $default, $target_context] = $this->process_target($trail[$t]->target, $trail[$t]->action, $parent_context);
         	 $parent_context = array_merge($parent_context, $target_context);

	 	 	 $all_css   = array_merge($all_css, $css);
	 	 	 $all_js    = array_merge($all_js, $js);
	 	 	 $all_meta  = array_merge($all_meta, $meta);
	 	 	 $all_title = $title ? $title : $all_title;
	 	 	 $all_html  = $t === 0 ? $html : preg_replace('/@content(.*?)@endcontent/', $html, $all_html);
	 	 	 if($t === count($trail) - 1 && $default){
	 	 	 	 $default_controller = $this->controllerrefs[$default] ?? '';
	 	 	 	 if($default_controller){
	 	 	 	     $trail[] = (Object)['url' => '', 'target' => $default_controller, 'action' => strtolower($this->request->route->method)]; //this must be checked, likely to be a problem
	 	 	 	 }
	 	 	 }
         }

         return [$all_css, $all_js, $all_meta, $all_title, $all_html];
     }

	 public function process(){
	 	 $trail = $this->request->trail;

	 	 [$all_css, $all_js, $all_meta, $all_title, $all_html] = $this->process_trail($trail);

	 	 $appstatic = AppStatic::init();
	 	 $all_css   = array_merge($appstatic::get_css_links(), $all_css);
	 	 $all_js    = array_merge($appstatic::get_js_links(), $all_js);

	 	 $appmeta   = AppMeta::init();
	 	 $all_meta  = array_merge($appmeta::get_meta_tags(), $all_meta);

         $pagetemplate = $this->get_template_file(Page::class);
	 	 $page = new View($pagetemplate);
	 	 $page->set_context([
	 	 	 'content' => $all_html, 
	 	 	 'title' => $all_title, 
	 	 	 'css' => implode("\n", array_unique($all_css)), 
	 	 	 'js' => implode("\n", array_unique($all_js)), 
	 	 	 'meta' => implode("\n", array_unique($all_meta))
	 	 ]);
	 	 echo $page->view();
	 }

	 private function process_target($target, $action, $parent_context = []){
         //inject global context data
         $global_context = AppContext::init()::get_context();

         //inject csrf token input here
         $token_key = CsrfMiddleware::get_token_key();
         $token     = CsrfMiddleware::get_token();
         $global_context[$token_key] = "<input type='hidden' id='".$token_key."' name='".$token_key."' value='".$token."'>";

         //get target response
	 	 if(!in_array($target, $this->controllerrefs)){ //this is a view without a controller
	 	 	 $template_file  = $this->templaterefs[$target];
	 	 	 $target_context = [];
	 	 	 $response       = array_merge($global_context, $parent_context);
	 	 }else{ //this is a controller
	 	 	 $template_file = $this->templaterefs[array_flip($this->controllerrefs)[$target]];
	 	 	 [$target_classname, $target_method] = $this->get_target_method($target, $action);
	 	     [$http_message, $context_from_parent] = $this->get_target_response($target_classname, $target_method, $parent_context);
	 	     $target_context = $http_message->get_response();
	 	     $response = array_merge($target_context, $global_context, $context_from_parent);
	 	 }

	 	 $view = new View($template_file);

	 	 $css     = $view->get_css();
	 	 $js      = $view->get_js();
	 	 $meta    = $this->set_template_context($view->get_meta(), $response, true);
	 	 $title   = $this->set_template_context($view->get_title(), $response, true);
	 	 $default = $view->get_default();
	 	 $blocks  = $view->get_blocks();

         foreach($blocks as $b){
         	 $response[$b] = "";
         	 $block_target = $this->controllerrefs[$b] ?? ( array_key_exists($b, $this->templaterefs) ? $b : '');
         	 if($block_target){
         	 	  $trail = [(Object)['url' => '', 'target' => $block_target, 'action' => strtolower($this->request->route->method)]]; //must recheck this. Will cause problems
         	 	  [$block_css, $block_js, $block_meta, $block_title, $block_html] = $this->process_trail($trail);
         	 	  $css  = array_merge($css, $block_css);
         	 	  $js   = array_merge($js, $block_js);
         	 	  $response[$b] = $block_html;
         	 }
         }
	 	 $view->set_context($response);
	 	 $html    = $view->view();
	 	 
	 	 return [$css, $js, $meta, $title, $html, $default, $target_context];
	 }
}
?>