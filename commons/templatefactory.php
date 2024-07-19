<?php
namespace SaQle\Commons;

class TemplateFactory{
	private array $template_paths = [];
	public function __construct(){
		 $apps = ['backoffice'];
		 foreach($apps as $app){
		 	$templates_path = DOCUMENT_ROOT."/apps/".$app."/templates";
		 	if(file_exists($templates_path)){
		 		 $dir = new \RecursiveDirectoryIterator($templates_path);
		 		 $file_counter = 0;
		 		 foreach (new \RecursiveIteratorIterator($dir) as $file){
					 $file_name = $file->getFileName();
					 if($file_name !== "." && $file_name !== ".."){
					 	 $abs_template_path = $templates_path."/".$file_name;

					 	 require_once $abs_template_path;
					 	 $template_method = str_replace(".php", "", $file_name);
					 	 $template_method = str_replace("_fun", "", $template_method);
					 	 if(function_exists($template_method)){
					 	 	 $file_counter++;

					 	 	 $reflection = new \ReflectionFunction($template_method);
			                 $attributes = $reflection->getAttributes();
					         $result = [];
						     foreach ($attributes as $attribute){
						         $result[$attribute->getName()] = $attribute->getArguments();
						     }

						     $tmp_interfaces = [];
						     $use_controller = "";
						     $use_parent     = "";
						     $get_controller = "";
						     $get_css = "";
						     $get_js = "";
						     $get_parent = "";
						     $class_name = ucwords($template_method);

						     if( array_key_exists("SaQle\Templates\Attributes\Controller", $result) ){
						     	 $tmp_interfaces[] = "HasController";
						     	 $long_controller_name = $result['SaQle\Templates\Attributes\Controller']['controller'];
						     	 $controller_parts = explode('\\', $long_controller_name);
						     	 $short_controller_name = end($controller_parts);
						     	 $class_name = $short_controller_name;
						     	 $use_controller = "use ".$long_controller_name." as ".$short_controller_name."Controller;";

						     	 $get_controller = "\n\tpublic function get_controller() : string{";
						     	 $get_controller .= "\n\t\treturn ".$short_controller_name."Controller::class;";
						     	 $get_controller .= "\n\t}";
						     }

						     if( array_key_exists("SaQle\Templates\Attributes\Css", $result) ){
						     	 $tmp_interfaces[] = "HasCss";
						     	 $css_files = $result['SaQle\Templates\Attributes\Css']['files'];
						     	 $css_files = array_map(function($f){return "'".$f."'";}, $css_files);
						     	 $get_css = "\n\tpublic function get_css() : array{";
						     	 $get_css .= "\n\t\treturn [".implode(", ", $css_files)."];";
						     	 $get_css .= "\n\t}";
						     }

						     if( array_key_exists("SaQle\Templates\Attributes\Js", $result) ){
						     	 $tmp_interfaces[] = "HasJs";
						     	 $js_files = $result['SaQle\Templates\Attributes\Js']['files'];
						     	 $js_files = array_map(function($f){return "'".$f."'";}, $js_files);
						     	 $get_js = "\n\tpublic function get_js() : array{";
						     	 $get_js .= "\n\t\treturn [".implode(", ", $js_files)."];";
						     	 $get_js .= "\n\t}";
						     }

						     if( array_key_exists("SaQle\Templates\Attributes\ParentTemplate", $result) ){
						     	 $tmp_interfaces[] = "HasParent";
						     	 $parent_parts = explode("/", $result['SaQle\Templates\Attributes\ParentTemplate']['path']);
						     	 $parent_parts = array_map(function($p){return ucwords($p);}, $parent_parts);
						     	 $use_parent = "use Booibo\\".implode("\\", $parent_parts).";";
						     	 $get_parent = "\n\tpublic function get_parent() : string{";
						     	 $get_parent .= "\n\t\treturn ".end($parent_parts)."::class;";
						     	 $get_parent .= "\n\t}";
						     }

                             $created_class = "<?php";
		                     $created_class .= "\nnamespace Booibo\Apps\\".ucwords($app)."\Templates;";
		                     $created_class .= "\n";
		                     $created_class .= "\nuse SaQle\Views\BaseView;";
		                     $created_class .= "\nuse SaQle\Templates\Interfaces\{".implode(", ", $tmp_interfaces)."};";
		                     $created_class .= $use_controller ? "\n".$use_controller : "";
		                     $created_class .= $use_parent ? "\n".$use_parent : "";
		                     $created_class .= "\n";
		                     $created_class .= "\nclass ".$class_name." extends BaseView implements ".implode(", ", $tmp_interfaces)."{";
		                     $created_class .= "\n";
		                     $created_class .= $get_controller;
		                     $created_class .= "\n";
		                     $created_class .= $get_css;
		                     $created_class .= "\n";
		                     $created_class .= $get_js;
		                     $created_class .= "\n";
		                     $created_class .= $get_parent;
		                     $created_class .= "\n\n\tpublic function get_template() : string{";
						     $created_class .= "\n\t\treturn \"".$template_method()."\";";
						     $created_class .= "\n\t}";
						     $created_class .= "\n}";
		                     $created_class .= "\n?>";

		                     // Read original file contents
		                     $original_contents = file_get_contents($abs_template_path);
		                     if($original_contents === false){
                             	exit();
                             }
		                     if(str_contains($abs_template_path, "_fun")){
	                             $function_template_path = $abs_template_path;
	                             $class_template_path = $templates_path."/".$template_method.".php";
		                     }else{
		                     	 $function_template_path = $templates_path."/".$template_method."_fun.php";
	                             $class_template_path = $abs_template_path;
		                     }
		                     echo 'Incoming path: '.$abs_template_path."\n";
		                     echo 'Function template: '.$function_template_path."\n";
		                     echo 'Class template: '.$class_template_path."\n";
		                     //print_r($result);
		                     echo "\\\\\\\\\\\\\n";

		                     if (file_put_contents($function_template_path, $original_contents) === false) {
							     exit();
							 }

							 if (file_put_contents($class_template_path, $created_class) === false) {
							     exit();
							 }

					 	 }
					 }
			    }
		 	}
		 }
	}
}
?>