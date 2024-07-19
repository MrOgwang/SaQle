<?php
namespace SaQle\Views;

use SaQle\Http\Request\Request;
use SaQle\Controllers\Attributes\{WebController, DbCtxClasses};
use SaQle\Commons\StringUtils;
use SaQle\Controllers\Trackers\ControllerTracker;
use SaQle\Services\Container\ContainerService;
use SaQle\Services\Container\Cf;

abstract class IView{
	 use StringUtils;
	 /**
	 * Template options
	 *
	 * @var TemplateOptions
	 */
	 private TemplateOptions $options;
	 
	 /**
	 * Request object
	 *
	 * @var Request
	 */
	 private Request $request;
	 
	 /**
	 * View attributes will be used to modify how a view and view template is processed before rendering.
	 * @var array
	 */
	 private array $attributes;

	 /**
	 * A collection of all css files to apply to this view.
	 * @var array
	 */
	 private array $css;

	 /**
	 * A collection of all js files to apply to this view.
	 * @var array
	 */
	 private array $js;
	 
	 /**
	 * Create a new view instance.
	 *
	 * @param TemplateOptions $options
	 * @param Request $request
	 */
	 public function __construct(Request $request, TemplateOptions $options){
		 $this->request = $request;
		 $this->options = $options;
		 $this->css     = [];
		 $this->js      = [];
		 //inject universal context data;
		 $this->inject_universal_context_data();
		 //load the template file
		 require_once $this->options->get_path();
		 //get all the attributes.

		 $template_method       = $this->options->get_template();
		 $reflection            = new \ReflectionFunction($template_method);
		 $this->attributes      = $this->get_template_attributes($reflection);
	 }
	 public function get_css(){
	 	 return $this->css;
	 }
	 public function get_js(){
	 	 return $this->js;
	 }
	 public function get_attributes(){
		 return $this->attributes;
	 }
	 public function get_request(){
		 return $this->request;
	 }
	 public function get_template_options(){
		 return $this->options;
	 }
	 public function set_context(array $context){
		 $this->options->set_context($context);
	 }
	 public function get_context(){
		 return $this->options->get_context();
	 }
	 /**
	 * Return the template string after all the context data have been filled in.
	 */
	 public function get_view(?string $view = null, ?array $context = null){
	 	 if(!$view){
	 	 	 $template_method = $this->options->get_template();
	 	 	 $view            = $template_method();
	 	 }
	 	 if(!$context){
	 	 	 $context = $this->options->get_context();
	 	 }
	 	 
	 	 return $this->set_template_context(template: $view, context_values: $context);
	 }

	 private function get_controller_db_contexts(string $controller_class){
         $reflector   = new \ReflectionClass($controller_class);
         $attributes  = $reflector->getAttributes(DbCtxClasses::class);
         $contexts    = [];
         if($attributes){
             $instance = $attributes[0]->newInstance();
             $classes = $instance->get_classes();
             foreach($classes as $cls){
                 $contexts[] = Cf::create(ContainerService::class)->createDbContext($cls);
             }
         }
         return $contexts;
     }

     private function is_child_loaded(array $children){
     	$child_loaded = false;
     	for($c = 0; $c < count($children); $c++){
     		$child_loaded = ControllerTracker::is_view_active($children[$c]);
     		if($child_loaded)
     			break;
     	}
     	return $child_loaded;
     }

	 public function render(bool $tobrowser = true, bool $callparent = true){
	 	 if(!$this->has_template_attribute()){
	 	 	 throw new \Exception("This is not a template!");
	 	 	 return;
	 	 }

	 	 ControllerTracker::add_active_view($this->options->get_template());
		 
		 $view = $this->get_view();

		 //if this view has css files.
		 if($this->has_css()){
		 	$this->css = array_key_exists("files", $this->attributes["SaQle\Templates\Attributes\Css"]) ? $this->attributes["SaQle\Templates\Attributes\Css"]["files"] : $this->attributes["SaQle\Templates\Attributes\Css"][0];
		 	$this->css = $this->css_names_to_links($this->css);
		 }
		 //if this view has js files.
		 if($this->has_js()){
		 	$this->js = array_key_exists("files", $this->attributes["SaQle\Templates\Attributes\Js"]) ? $this->attributes["SaQle\Templates\Attributes\Js"]["files"] : $this->attributes["SaQle\Templates\Attributes\Js"][0];
		 	$this->js = $this->js_names_to_links($this->js);
		 }
		 
		 //if there are context data that should be sourced from other controllers, 
		 $template_params = $this->attributes["SaQle\Templates\Attributes\Template"];
		 if(array_key_exists("context", $template_params)){
			 $context = $template_params['context'];
			 foreach($context as $key => $d){
				 if(!is_numeric($key)){
				 	 $context_instance = new $d(request: $this->request, context: $this->get_controller_db_contexts($d));
				 	 $web_instance     = $context_instance->web_instance();
					 $this->options->add_to_context($key, $web_instance->view(tobrowser: false));
					 $this->css = array_merge($this->css, $web_instance->get_view_css());
	                 $this->js  = array_merge($this->js,  $web_instance->get_view_js());
				 }
			 }
		 }

         //echo $this->options->get_template()."\n";
         //print_r(ControllerTracker::get_active_views());
		 //load context from the default child controller if there is any.
		 if(array_key_exists("default_child", $template_params) && $template_params['default_child'] && array_key_exists("children", $template_params)){
		 	 $children = $template_params['children'];
		 	 $default_child = array_values($template_params['default_child'])[0];
		 	 $child_loaded = $this->is_child_loaded($children);
		 	 if(!$child_loaded){
		 	 	 /**
		 	 	 * There is a default child to be loaded: It can either be a controller, 
		 	 	 * a template view or a method of the current template's controller that returns a controller.
		 	 	 * */
		 	 	 $default_child_key = array_keys($template_params['default_child'])[0];
		 	 	 if(str_starts_with($default_child, '@method:')){
		 	 		 if(!$this->has_controller_attribute()){
		 	 			 throw new \Exception("@METHOD must be defined within the template's controller!");
		 	 		 }
	                
	                 $current_controller   = array_values($this->attributes["SaQle\Templates\Attributes\Controller"])[0];
		 	 		 $default_child_method = explode(":", $default_child)[1];
		 	 		 if(!method_exists($current_controller, $default_child_method)){
		 	 			 throw new \Exception("@METHOD not defined within the template's controller!");
		 	 		 }

	                 $default_child = (new $current_controller(request: $this->request, context: $this->get_controller_db_contexts($current_controller)))->$default_child_method();
		 	 	 }
		 	 
		 	 	 $dbcontexts     = $this->get_controller_db_contexts($default_child);
		 	 	 $child_instance = new $default_child(request: $this->request, context: $dbcontexts);
	 	 	     $web_instance   = $child_instance->web_instance();
			     $this->options->add_to_context($default_child_key, $web_instance->view(tobrowser: false, callparent: false));
			     $this->css = array_merge($this->css, $web_instance->get_view_css());
	             $this->js  = array_merge($this->js,  $web_instance->get_view_js());
	         }
		 }

		 //if this template was not called from a controller and it has a controller, call the controller to set context data before rendering template.
		 if(!$this->options->get_from_controller() && $this->has_controller_attribute()){
		 	 $controllers = $this->attributes["SaQle\Templates\Attributes\Controller"];
		 	 foreach($controllers as $c){
		 	 	 $cinstance = new $c(request: $this->request, context: $this->get_controller_db_contexts($c));
				 $this->options->set_context(array_merge($this->options->get_context(), $cinstance->web_instance()->get_context()));
			 }
		 }

         if($callparent){

             $parent_template = null;
             if($this->has_controller_attribute()){
             	$ctrler          = array_values($this->attributes["SaQle\Templates\Attributes\Controller"])[0];
             	$ctrler_instance = new $ctrler(request: $this->request, context: $this->get_controller_db_contexts($ctrler));
             	$parent_template = $ctrler_instance->get_desired_parent_template();
             }

         	 //if this template has a parent template, call the parent template view render as you collect the css and js files.
			 if($parent_template || $this->has_parent_template_attribute()){
			 	 $template_params = $this->attributes["SaQle\Templates\Attributes\ParentTemplate"];
			 	 $path = $parent_template ? $parent_template['path'] ?? null        : $template_params['path'] ?? null;
			 	 $name = $parent_template ? $parent_template['name'] ?? null        : $template_params['name'] ?? null;
			 	 $key  = $parent_template ? $parent_template['context_key'] ?? null : $template_params['context_key'] ?? null;

			 	 if($path && $name && $key){
			 	 	 $path                 = $this->get_complete_path($path);
					 $template_options     = new TemplateOptions(template: $name, path: $path, from_controller: false);
			 	 	 $vinstance            = new TemplateView(request: $this->request, options: $template_options);
		             $parent_view          = $vinstance->render(tobrowser: false);
		             $parent_context       = $vinstance->get_template_options()->get_context();
		             $parent_context[$key] = $view;
		             $view                 = $this->get_view($parent_view, $parent_context);
		             $this->css            = array_merge($this->css, $vinstance->get_css());
		             $this->js             = array_merge($this->js, $vinstance->get_js());
			 	 }
			 }   
         }

	 	 if($tobrowser){
	 	 	 //Inject css and js files into the context
			 $this->options->add_to_context('css_files', implode("", array_reverse($this->css)));
			 $this->options->add_to_context('js_files', implode("", array_reverse($this->js)));
			 $this->options->add_to_context('og_meta_data', '');
			 $this->options->add_to_context('site_title', '');
	 	     echo $this->get_view($view);
	 	 	 return;
	 	 }
	 	 return $view;
	 }
     private function get_template_attributes(\Reflector $reflection){
     	 $attributes = $reflection->getAttributes();
         $result = [];
	     foreach ($attributes as $attribute){
	         $result[$attribute->getName()] = $attribute->getArguments();
	     }
	     return $result;
     }
     private function has_template_attribute() : bool{
     	 return array_key_exists("SaQle\Templates\Attributes\Template", $this->attributes);
     }
     private function is_top_level() : bool{
     	 $is_toplevel = false;
     	 if($this->has_template_attribute()){
     	 	$template_params = $this->attributes["SaQle\Templates\Attributes\Template"];
     	 	if(array_key_exists("is_toplevel", $template_params) && $template_params["is_toplevel"] == true){
     	 		$is_toplevel = true;
     	 	}
     	 }
     	 return $is_toplevel;
     }
     private function has_parent_template_attribute() : bool{
     	 return array_key_exists("SaQle\Templates\Attributes\ParentTemplate", $this->attributes);
     }
     private function has_controller_attribute() : bool{
         return array_key_exists("SaQle\Templates\Attributes\Controller", $this->attributes);
     }
     private function has_css() : bool{
         return array_key_exists("SaQle\Templates\Attributes\Css", $this->attributes);
     }
     private function has_js() : bool{
         return array_key_exists("SaQle\Templates\Attributes\Js", $this->attributes);
     }
     private function get_complete_path($path){
     	return DOCUMENT_ROOT."/".$path.".php";
     }
	 private function css_names_to_links(array $names){
	 	 $path  = ROOT_DOMAIN."static/css/";
	 	 $links = [];
	 	 foreach ($names as $n){
	 	 	 $css_file_path = $path.$n.".css";
	 	     $links[] = "<link href='{$css_file_path}' rel='stylesheet'>";
	 	 }
	 	 return $links;
	 }
	 private function js_names_to_links(array $names){
	 	 $path = ROOT_DOMAIN."static/js/";
	 	 $links = [];
	 	 foreach ($names as $n){
	 	 	 $js_file_path = $path.$n.".js";
	 	     $links[] = "<script src='{$js_file_path}'></script>";
	 	 }
	 	 return $links;
	 }
	 private function inject_universal_context_data(){
		 $this->options->add_to_context('base_url', BASE_URL);
		 $this->options->add_to_context('layout_image_path', LAYOUT_IMAGE_PATH);
		 $this->options->add_to_context('icons_image_path', ICONS_IMAGE_PATH);
	 }
}
?>