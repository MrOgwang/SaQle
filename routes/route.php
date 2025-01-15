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
         $this->method         = $_SERVER['REQUEST_METHOD'];
	 }
     
	 public function matches() : array{
        
         $this->url = rtrim($this->url, '/');
         $url_parts = parse_url($_SERVER['REQUEST_URI']);
         $path      = str_ends_with($url_parts['path'], '/') ? rtrim($url_parts['path'], '/') : $url_parts['path'];
         $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $this->url);
         if(preg_match('#^'.$pattern.'$#', $path, $matches)){
             //Pass the captured parameter values as named arguments to the target function
             $this->params  = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Only keep named subpattern matches
             if(array_key_exists('query', $url_parts)){
                 parse_str($url_parts['query'], $queries);
                 $this->queries = $queries;
             }
             return in_array($this->method, $this->methods) ? [true, true] : [true, false];
         }

         return [false, false];
    }
    public function get_target(){
       return $this->target;
    }
    public function get_method(){
      return $this->method;
    }
    public function get_methods(){
      return $this->methods;
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