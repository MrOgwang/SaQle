<?php
namespace SaQle\Http\Request\Processors;

use SaQle\Controllers\Page;
use ReflectionClass;
use SaQle\Views\View;
use SaQle\Controllers\Refs\ControllerRef;
use SaQle\Commons\StringUtils;
use SaQle\Http\Request\Middleware\CsrfMiddleware;
use SaQle\Controllers\Interfaces\WebController;
use SaQle\Core\FeedBack\ExceptionFeedBack;
use SaQle\Templates\Template;
use SaQle\Auth\Models\GuestUser;

class WebRequestProcessor extends RequestProcessor{
	 use StringUtils;

     private $controllerrefs = [];
     private $templaterefs   = [];

     public function __construct(){
     	 $this->controllerrefs = ControllerRef::init()::get_controllers();
     	 $this->templaterefs = ControllerRef::init()::get_views();
     	 parent::__construct();
     }

     private function process_trail(array $trail, array $feedback_context){
     	 $all_css   = [];
         $all_js    = [];
         $all_meta  = [];
         $all_title = "";
         $all_html  = "";

         $parent_context = [];
         for($t = 0; $t < count($trail); $t++){
         	 [$css, $js, $meta, $title, $html, $default, $target_context] = $this->process_target($trail[$t]->target, $trail[$t]->action, $parent_context, $feedback_context);
         	 $parent_context = array_merge($parent_context, $target_context);

	 	 	 $all_css   = array_merge($all_css, $css);
	 	 	 $all_js    = array_merge($all_js, $js);
	 	 	 $all_meta  = array_merge($all_meta, $meta);
	 	 	 $all_title = $title ? $title : $all_title;
	 	 	 $all_html  = $t === 0 ? $html : preg_replace('/@content(.*?)@endcontent/', $html, $all_html);
	 	 	 
	 	 	 if($t === count($trail) - 1 && $default){
	 	 	 	 $ctrl = $trail[$t]->target;
	 	 	 	 $ctrlinstance = new $ctrl();
	 	 	 	 if( in_array($trail[$t]->target, $this->controllerrefs) && $ctrlinstance instanceof WebController ){
	 	 	 	 	 $default = $ctrlinstance->get_default();
	 	 	 	 }

	 	 	 	 $default_controller = $this->controllerrefs[$default] ?? '';
	 	 	 	 if($default_controller){
	 	 	 	     $trail[] = (Object)['url' => '', 'target' => $default_controller, 'action' => 'get']; //this must be checked, likely to be a problem
	 	 	 	 }
	 	 	 }
         }
         return [$all_css, $all_js, $all_meta, $all_title, $all_html, $parent_context];
     }

	 public function process(){
	 	 $trail = $this->request->trail;

         $efb = ExceptionFeedBack::init();
	 	 $feedback_context = $efb->acquire_context();

	 	 [$all_css, $all_js, $all_meta, $all_title, $all_html, $trail_context] = $this->process_trail($trail, $feedback_context);

         $pagetemplate = $this->templaterefs['page'];
	 	 $page = new View($pagetemplate, $this->request->user ?? new GuestUser());
	 	 $page->set_context([
	 	 	 'content' => $all_html, 
	 	 	 'title' => $all_title, 
	 	 	 'css' => implode("\n", array_unique($all_css)), 
	 	 	 'js' => implode("\n", array_unique($all_js)), 
	 	 	 'meta' => implode("\n", array_unique($all_meta))
	 	 ]);
	 	 echo $page->view();
	 }

	 private function process_target($target, $action, $parent_context = [], $feedback_context = []){
         //inject global context data
         $global_context = Template::init()::get_context();

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
	 	 	 $template_file = $this->templaterefs[array_flip($this->controllerrefs)[$target]] ?? '';
	 	 	 //[$target_classname, $target_method] = $this->get_target_method($target, $action);
	 	     //[$http_message, $context_from_parent] = $this->get_target_response($target_classname, $target_method, $parent_context);
	 	     [$http_message, $context_from_parent] = $this->get_target_response($target, $action, $parent_context);

	 	     $target_context = $http_message->data;
	 	     $target_context['http_response_code'] = $http_message->code;
	 	     $target_context['http_response_message'] = $http_message->message;
	 	     $response = array_merge($global_context, $context_from_parent, $target_context, $feedback_context);
	 	 }
	 	 
	 	 $view = new View($template_file, $this->request->user ?? new GuestUser());

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
         	 	  $trail = [(Object)['url' => '', 'target' => $block_target, 'action' => 'get']];
         	 	  [$block_css, $block_js, $block_meta, $block_title, $block_html, $trail_context] = $this->process_trail($trail, $feedback_context);
         	 	  $css  = array_merge($css, $block_css);
         	 	  $js   = array_merge($js, $block_js);
         	 	  $response[$b] = $block_html;
         	 	  $response     = array_merge($response, $trail_context);
         	 }
         }
	 	 $view->set_context($response);
	 	 $html    = $view->view();
	 	 
	 	 return [$css, $js, $meta, $title, $html, $default, $target_context];
	 }
}
?>