<?php
namespace SaQle\Controllers\Attributes;

use Attribute;
use SaQle\Http\Request\Request;
use SaQle\Views\TemplateView;
use SaQle\Views\TemplateOptions;

#[Attribute(Attribute::TARGET_CLASS)]
class WebController{

    /**
      * Current request object
      * @var Request
      * */
    private Request $request;

    /**
      * An array of expected url parameters
      * @var array
      * */
    private ?array $params;

	 /**
 	 * The template name and path to connect to this controller
 	 * @var string
 	 */
	 private string $template_name;
	 private string $template_path;

	 /**
	 * The template view instance for this web controller
	 * @var TemplateView
	 */
	 private TemplateView $view;

	 /**
	  * Context data for the template
	  * @var array
	  * */
	 private array $context;

	 /**
     * Create a new controller instance.
     * @throw TemplateNotFoundException
     */
	 public function __construct(?string $template = null, ?array $params = null){
	 	  $this->set_template($template);
		  $this->params = $params;
	 }

	 public function set_template(string $template){
	 	   $name_parts     = explode(".", $template);
	 	   if(!$name_parts){
            //throw a template path not provided exception.
       }

	 	   $this->template_path  = $this->get_template_path($name_parts);
	 	   $this->template_name  = $this->get_template_name($name_parts);

	 	   /**
	 	   * Check that the file pointed by template path exists and throw an exception otherwise.
	 	   */ 
		   if(!file_exists($this->template_path)){
			     throw new \exception("Invalid or missing template file!");
		   }
	 }

	 private function get_template_path(array $name_parts) : string{
        return count($name_parts) === 1 ? DOCUMENT_ROOT."/templates/".$name_parts[0].".php" 
        : DOCUMENT_ROOT."/apps/".$name_parts[0]."/templates/".$name_parts[1].".php";
     }

     private function get_template_name(array $name_parts) : string{
        return count($name_parts) === 1 ? $name_parts[0] : $name_parts[1];
     }

     private function extract_context_from_request(){
	 	foreach($this->context as $dk => $dv){
	 		if($this->request->data->exists($dk)){
	 			$this->context[$dk] = $this->request->data->get($dk, '');
	 		}
	 	}
	 }

	 public function init(Request $request, array $context, string $og_meta_data = "", string $title = ""){
	 	 $this->request = $request;
	 	 $this->context = $context;
	 	 /**
	 	  * Check that expected params are present.
	 	  * */
	 	 $params = $this->request->route->get_params();
	 	 $all_params_provided = true;
	 	 if($this->params){
	 	 	foreach($this->params as $p){
	 	 		if(!array_key_exists($p, $params)){
	 	 			$all_params_provided = false;
	 	 		}
	 	 	}
	 	 }

	 	 if(!$all_params_provided){
	 	 	throw new \exception("Some or all required url parameters are missing!");
	 	 }

	 	 $this->extract_context_from_request();
	 	 $this->view = new TemplateView(
	 	 	 $this->request, 
	 	 	 new TemplateOptions($this->template_name, $this->template_path, $this->context), 
	 	 	 $og_meta_data, 
	 	   $title
	 	 );
	 }

	 public function get_context(){
	 	return $this->context;
	 }
	 
	 public function get_request(){
		 return $this->request;
	 }
	 
	 public function get_view(){
		 return $this->view;
	 }
	 
	 public function get_view_css(){
		 return $this->view->get_css();
	 }
	 
	 public function get_view_js(){
		 return $this->view->get_js();
	 }

	 public function view(bool $tobrowser = true, bool $callparent = true){
		 $this->view->set_context(array_merge($this->view->get_context(), $this->context));
		 #render or return view to caller.
	 	 if($tobrowser){
         //header("Cross-Origin-Opener-Policy: same-origin");
         //header("Cross-Origin-Embedder-Policy: require-corp");
			   echo $this->view->render($tobrowser, $callparent);
		 }else{
			 return $this->view->render($tobrowser, $callparent);
		 }
	 }
}
?>