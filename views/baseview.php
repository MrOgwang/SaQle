<?php
namespace SaQle\Views;

use SaQle\Http\Request\Request;
use SaQle\Templates\Interfaces\{HasController, HasCss, HasJs, HasParent, HasContext, HasChildren};

use SaQle\Controllers\Attributes\{WebController, DbCtxClasses};
use SaQle\Commons\StringUtils;

abstract class BaseView{
	 use StringUtils;
	 
	 /**
	 * Create a new view instance.
	 * @param Request $request
	 */
	 public function __construct(protected Request $request){}

     /**
	 * Return the template string after all the context data have been filled in.
	 */
	 public function get_view(?string $view = null, ?array $context = null){
	 	 if(!$view){
	 	 	 $view = $this->get_template();
	 	 }

	 	 if(!$context){
	 	 	 
	 	 }
	 	 
	 	 return $this->set_template_context(template: $view, context_values: $context);
	 }

	 public function __invoke(bool $tobrowser = true, bool $callparent = true){
		 $css  = [];
		 $js   = [];
		 $data = [];

		 #collect css
	 	 if($this instanceof HasCss){ 
	 	 	$css = array_merge($css, $this->get_css());
	 	 	//$this->css = $this->css_names_to_links($this->css);
	 	 }

	 	 #collect js
	 	 if($this instanceof HasJs){
	 	 	$js = array_merge($js, $this->get_js());
	 	 	//$this->js = $this->js_names_to_links($this->js);
	 	 }

         #if there are context data that should be sourced from other views
	 	 if($this instanceof HasContext){
	 	 	 foreach($this->get_context() as $ctx_key => $ctx_class){
	 	 	 	$ctx_instance = new $ctx_class(request: $this->request);
	 	 	 	[$_css, $_js, $_view] = $ctx_instance(tobrowser: false);
	 	 	 	$css = array_merge($css, $_css);
	 	 	 	$js = array_merge($js, $_css);
	 	 	 	$data[$ctx_key] = $_view;
	 	 	 }
	 	 }

		 #load default child view if any.
		 if($this instanceof HasChildren){
		 	 $default_child = $this->get_children()[0] ?? null;
		 	 $content_key = $this->get_content_key() ?? null;
		 	 if($default_child && $content_key){
		 	 	 $child_instance = new $default_child(request: $this->request);
		 	 	 [$_css, $_js, $_view, ] = $child_instance(tobrowser: false);
		 	 	 $css = array_merge($css, $_css);
	 	 	 	 $js = array_merge($js, $_css);
	 	 	 	 $data[$ctx_key] = $_view;
		 	 }
		 }

		 #call the view's controller to get data
		 if($this instanceof HasController){
		 	$controller = $this->get_controller();
		 }

		 $current_view = $this->get_view(context: $data);

         #load the view's parent
         if($this instanceof HasParent && $callparent){
	 		 $parent_template = $this->get_parent();
			 if($parent_template){
			 	 $parent_instance = new $parent_template(request: $this->request);
			 	 $parent_content_key = $parent_instance->get_content_key();
		 	 	 [$_css, $_js, $_view, $_data] = $parent_instance(tobrowser: false);
		 	 	 $css = array_merge($css, $_css);
	 	 	 	 $js = array_merge($js, $_css);
	 	 	 	 $_data['parent_content_key'] = $current_view;
	 	 	 	 $current_view = $this->get_view(view: $_view, context: $_data);
	 	 	 	 $data = $_data;
			 }   
         }

         #inject universal context to data.
         $this->inject_universal_context_data($data);

	 	 if($tobrowser){
	 	 	 $css = $this->css_names_to_links($css);
	 	 	 $js = $this->js_names_to_links($js);
	 	 	 $data['css_files'] = implode("", array_reverse($css));
	 	 	 $data['js_files'] = implode("", array_reverse($js));
	 	 	 $data['og_meta_data'] = "";
	 	 	 $data['site_title'] = "";
	 	     echo $this->get_view(view: $current_view, context: $data);
	 	 	 return;
	 	 }

	 	 return [$css, $js, $current_view, $data];
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
	 
	 private function inject_universal_context_data(&$data){
		 $data['base_url']          = BASE_URL;
		 $data['layout_image_path'] = LAYOUT_IMAGE_PATH;
		 $data['icons_image_path']  = ICONS_IMAGE_PATH;
	 }
}
?>