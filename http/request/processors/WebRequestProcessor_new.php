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
	 	 if(str_starts_with($_SERVER['REQUEST_URI'], MEDIA_URL) || str_starts_with($_SERVER['REQUEST_URI'], CRON_URL)){
             //serve media file
	 	 	 $tc = count($this->request->trail);
	 	 	 $this->serve_media($this->request->trail[$tc - 1]->target, $this->request->trail[$tc - 1]->action);
         }else{
         	 //get the request trail
         	 $trail = $this->request->trail;

         	 //gather all the context data in one place
         	 $context = [];

         	 foreach($trail as $t){
         	 	 if(class_exists($t->target)){
         	 	 	 $http_message = $this->get_target_response($t->target, $t->action);
         	 	 	 $context = array_merge($context, $http_message->data);
         	 	 	 $context['http_response_code'] = $http_message->code;
	 	             $context['http_response_message'] = $http_message->message;
         	 	 }
         	 }

         	 /**
         	 * call default and block controllers to fill in their context data as you find the comipled template for this route
         	 * */
         	 $focused_url = $this->request->route->url."/";
         	 $mappings_file = DOCUMENT_ROOT.CLASS_MAPPINGS_DIR."routes.php";
         	 $route_mappings = file_exists($mappings_file) ? require_once $mappings_file : [];
         	 $extra_controllers = [];
         	 $template_path = '';

         	 if(array_key_exists($focused_url, $route_mappings)){
         	 	 $extra_controllers = $route_mappings[$focused_url]['extra_controllers'];
         	 	 $template_path = $route_mappings[$focused_url]['template_path'];

         	 	 echo "Template: $template_path\n";

         	 	 foreach($extra_controllers as $et){
         	 	 	 $et_http_message = $this->get_target_response($et->target, $et->action);
         	 	 	 //echo "Target: $et->target\n";
         	 	 	 //print_r( $et_http_message);
         	 	 	 //echo "\n-----------------------\n";

         	 	 	 $context = array_merge($context, $et_http_message->data ?? []);
         	 	 }
         	 }

         	 //add feedback context
	         $efb = ExceptionFeedBack::init();
	         $context = array_merge($context, $efb->acquire_context());

	         //inject global context data
	         $context = array_merge($context, Template::init()::get_context());

             //inject csrf token input here
             $token_key = CsrfMiddleware::get_token_key();
             $token     = CsrfMiddleware::get_token();

             $context[$token_key] = "<input type='hidden' id='".$token_key."' name='".$token_key."' value='".$token."'>";

             //inject the user
             $context['session_user'] = $this->request->user ?? new GuestUser();

             if($template_path){
             	 $page = new View($template_path);
			 	 $page->set_context($context);
		 	     echo $page->view();

		 	     return;
             }

		 	 echo "";
         }
	 }

     private function serve_media($target, $action){
     	 [$http_message, $context_from_parent] = $this->get_target_response($target, $action, []);
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
