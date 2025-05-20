<?php
/**
 * A route object
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use Closure;
use SaQle\Permissions\IsAuthorized;
use SaQle\Core\Assert\Assert;
use SaQle\Http\Request\Data\Data;
use SaQle\Routes\Interfaces\IRoute;
use SaQle\Controllers\ProxyController;

class Route implements IRoute{
     //the http methods that the route will handle
     public protected(set) array $methods = [] {
         set(array $value){
             $this->methods = $value;
         }

         get => $this->methods;
     }

     //the url to match this route with
     public protected(set) string $url = '' {
         set(string $value){
             $this->url = $value;
         }

         get => $this->url;
     }

     //the path parameters extracted from route
     public Data $params {
         set(Data $value){
             $this->params = $value;
         }

         get => $this->params;
     }

     //the query parameters extracted from the route
     public Data $queries {
         set(Data $value){
             $this->queries = $value;
         }

         get => $this->queries;
     }

     //the actual http request method
     public protected(set) string $method = '' {
         set(string $value){
             $this->method = $value;
         }

         get => $this->method;
     }

     //the actual http request action
     public string $action = '' {
         set(string $value){
             $this->action = $value;
         }

         get => $this->action;
     }

     /**
      * the name of the view to return 
      * or the controller class name where the action method is defined
      * */
     public string $target = '' {
         set(string $value){
             $this->target = $value;
         }

         get => $this->target;
     }

	 /**
      * if the target is a controller class name,
      * this is an array of action methods that will be executed to get response.
      * 
      * This array must match the array of http methods
      * */
     public protected(set) ?array $actions = null {
         set(?array $value){
             $this->actions = $value;
         }

         get => $this->actions;
     }

     //create a new route object
	 public function __construct(string $url, string $target, ?array $actions = null){
         $this->params  = new Data();
         $this->queries = new Data();
		 $this->url     = $url;
         $this->target  = $target;
         if(is_a($target, ProxyController::class, true)){
             $proxy = new $target();
             $this->target = $proxy->controller::class;
             $actions = $proxy->get_actions();
         }
         $this->method = $_SERVER['REQUEST_METHOD'];

         /**
          * if actions is not provided, fill in with default handlers that correspond with http names
          * */
         if(is_null($actions)){
             $this->methods = ['POST', 'PUT', 'GET', 'PATCH'];
             $this->actions = ['post' => 'post', 'get' => 'get', 'put' => 'put', 'patch' => 'patch'];
         }else{
             //ensure the methods array is an array of strings with valid http methods
             $methods = [];
             $tmpactions = [];
             foreach($actions as $m => $a){
                 Assert::inArray(strtoupper($m), ['POST', 'PUT', 'GET', 'PATCH'], $m.' is not a valid http method. Defined in route '.$this->url);
                 $methods[] = strtoupper($m);
                 $tmpactions[strtolower($m)] = $a;
             }

             $this->methods = $methods;
             $this->actions = $tmpactions;
         }

         /**
          * The default action on all controllers is the action associated to the get method. 
          * When the matching route is determined, its action will be set to the appropriate http method
          * */
         $this->action = $this->actions['get'] ?? 'get';
	 }

     /**
      * determine whether a route matches the current request or not
      * a match is done for both the route and the http method.
      * 
      * @returns array of bool values. route match and a method match
      * */
	 public function matches() : array {
         $this->url = rtrim($this->url, '/');
         $url_parts = parse_url($_SERVER['REQUEST_URI']);
         $path      = str_ends_with($url_parts['path'], '/') ? rtrim($url_parts['path'], '/') : $url_parts['path'];
         $pattern   = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $this->url);
         if(preg_match('#^'.$pattern.'$#', $path, $matches)){
             $params  = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
             foreach($params as $p => $pv){
                 $this->params->set($p, $pv);
             }

             if(array_key_exists('query', $url_parts)){
                 parse_str($url_parts['query'], $queries);
                 foreach($queries as $q => $qv){
                     $this->queries->set($q, $qv);
                 }
             }
             return in_array($this->method, $this->methods) ? [true, true, $this] : [true, false];
         }

         return [false, false];
     }
}
