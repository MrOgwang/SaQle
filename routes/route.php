<?php
declare(strict_types = 1);
namespace SaQle\Routes;

use Closure;
use SaQle\Permissions\IsAuthorized;

class Route{
	 private array  $methods;
     private string $method;
	 private string $url;
	 private string|Closure $target;
	 private array  $params;
     private array  $queries  = [];
	 public function __construct(array $methods, string $url, string|Closure $target){
		 $this->methods        = $methods;
		 $this->url            = $url;
		 $this->target         = $target;
	 }

     public function get_actual_template_path(string $symbolic_path) : string{
        $path_array = explode(".", $symbolic_path);
        if(!$path_array){
            //throw a template path not provided exception.
        }

        return count($path_array) === 1 ? DOCUMENT_ROOT."/templates/".$path_array[0].".php" : DOCUMENT_ROOT."/apps/".$path_array[0]."/templates/".$path_array[1].".php";
     }

     public function get_actual_template_name(string $symbolic_path) : string{
        $path_array = explode(".", $symbolic_path);
        if(!$path_array){
            //throw a template path not provided exception.
        }

        return count($path_array) === 1 ? $path_array[0] : $path_array[1];
     }

     public function is_api_request() : bool{
        $is_api_request = false;
        for($u = 0; $u < count(API_URL_PREFIXES); $u++){
            if(str_contains($this->url, API_URL_PREFIXES[$u])){
                $is_api_request = true;
                break;
            }
        }
        return $is_api_request;
     }

     public function is_sse_request() : bool{
        $is_sse_request = false;
        for($u = 0; $u < count(SSE_URL_PREFIXES); $u++){
            if(str_contains($this->url, SSE_URL_PREFIXES[$u])){
                $is_sse_request = true;
                break;
            }
        }
        return $is_sse_request;
     }

	public function matches() : bool{
        $this->method = $_SERVER['REQUEST_METHOD'];
        $url          = $_SERVER['REQUEST_URI'];
        if(in_array($this->method, $this->methods)){
        	 // Use named subpatterns in the regular expression pattern to capture each parameter value separately
             $this->url = rtrim($this->url, '/');
             $url_parts = parse_url($url);
             $path      = str_ends_with($url_parts['path'], '/') ? rtrim($url_parts['path'], '/') : $url_parts['path'];
             $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $this->url);
             if(preg_match('#^'.$pattern.'$#', $path, $matches)){
                // Pass the captured parameter values as named arguments to the target function
                $this->params  = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Only keep named subpattern matches
                if(array_key_exists('query', $url_parts)){
                     parse_str($url_parts['query'], $queries);
                     $this->queries = $queries;
                }
                return true;
             }
         }
         return false;
    }
    public function get_target(){
       return $this->target;
    }
    public function get_method(){
      return $this->method;
    }
    public function get_url(){
      return $this->url;
    }
    public function get_params(){
        return $this->params;
    }
    public function get_queries(){
        return $this->queries;
    }
    public function get_query_param($param_name, $default = ''){
        return $this->queries && array_key_exists($param_name, $this->queries) ? $this->queries[$param_name] : $default;
    }
    public function set_method($method){
        $this->method = $method;
    }
    public function set_target($target){
        $this->target = $target;
    }
}
?>