<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The router object is used to define application routes
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use SaQle\Core\Support\RouteResolver;
use SaQle\Http\Request\RequestScope;
use RuntimeException;

final class Router {

     public static array $aliases = [];

     protected static array $routes = [];

     protected static array $context_stack = [];

     //only one instance of router must exist
     protected static ?self $instance = null;

     private function __construct(){}

     protected static function instance(): self {
         if (!static::$instance) {
             static::$instance = new static();
         }

         return static::$instance;
     }
     
     /**
      * Define a route for a get request
      * 
      * @param string $url 
      *     - the url to match for this route
      * 
      * @param string $target 
      *     - the name of the component whose controller will process request.
      * 
      * The target should be provided in one of these formats:
      * 
      * component_name : component lives in the global namespace,
      *                  and the controller method to be executed will be determined from the request
      *                  http method
      * 
      * component_name@method_name : No guessing controller method to execute
      * 
      * module.component_name : component lives in a module.
      * 
      * module.component_name@method_name 
      * 
      * */
     static public function get(string $url, string $target, ?string $model_class = null) : Router {
    
         $route = new Route('get', $url, $target, $model_class);
        
         self::apply_context($route);
         
         self::$routes[] = [$route->key => $route];

         return static::instance();
     }

     //parameters as descirbed in get
     static public function post(string $url, string $target, ?string $model_class = null) : Router {
        
         $route = new Route('post', $url, $target, $model_class);
    
         self::apply_context($route);

         self::$routes[] = [$route->key => $route];

         return static::instance();
     }

     //parameters as descirbed in get
     static public function patch(string $url, string $target, ?string $model_class = null) : Router {

         $route = new Route('patch', $url, $target, $model_class);

         self::apply_context($route);

         self::$routes[] = [$route->key => $route];

         return static::instance();
     }

     //parameters as descirbed in get
     static public function put(string $url, string $target, ?string $model_class = null) : Router {

         $route = new Route('put', $url, $target, $model_class);
        
         self::apply_context($route);

         self::$routes[] = [$route->key => $route];

         return static::instance();
     }

     //parameters as descirbed in get
     static public function delete(string $url, string $target, ?string $model_class = null) : Router {

         $route = new Route('delete', $url, $target, $model_class);
        
         self::apply_context($route);

         self::$routes[] = [$route->key => $route];

         return static::instance();
     }

     public static function resolve(string $method, string $url, string $resolver_class){

         if(!is_a($resolver_class, RouteResolver::class, true)){
             throw new RuntimeException("The class {$resolver_class} must be an instance of a ".RouteResolver::class);
         }

         $resolver_instance = resolve($resolver_class);

         $variant_routes = [];

         foreach($resolver_instance->routes() as $variant_name => $variant_callback){
             $variant_routes[$variant_name] = $variant_callback(new Route($method, $url, "", null));
         }

         $route = new DeferedRoute($method, $url, $variant_routes, $resolver_class);
         
         self::apply_context($route);

         self::$routes[] = [$route->key => $route];
     }

     public static function all(): array {
         return self::$routes;
     }

     public static function clear(): void {
         self::$routes = [];
     }

     /**
      * Provide a custom route name.
      * 
      * @var string name - name of route
      * */
     public function name(string $name){

         $route = array_values(self::$routes[count(self::$routes) - 1])[0];

         $route->name($name);

         return $this;
     }

     /**
      * Provide a custom route prefix
      * 
      * @var string prefix 
      * */
     public function prefix(string $prefix){

         $route = array_values(self::$routes[count(self::$routes) - 1])[0];

         $route->prefix($prefix);

         return $this;
     }

     /**
      * Provide a route scope
      * 
      * @var RequestScope $scope
      * */
     public function scope(RequestScope $scope){

         $route = array_values(self::$routes[count(self::$routes) - 1])[0];

         $route->scope($scope);
         
         return $this;
     }

     /**
      * Customize event meta data for event stream
      * routes.
      * 
      * @var string event - the name of event
      * @var int interval - the interval for sleep
      * */
     public function sse(string $event, int $interval){

         $route = array_values(self::$routes[count(self::$routes) - 1])[0];

         $route->sse($event, $interval);
         
         return $this;
     }

     /**
      * For web requests, the route will declare which components
      * the final UI layout will be composed with
      * 
      * @param array $layouts: an array of components to compose the layout from. This can be an array of strings,
      * or an array of arrays of strings.
      * 
      * When an array of arrays of strings is provided, the resolver must be provided to determine
      * which layout group to use.
      * */
     public function layout(array $layouts){

         $route = array_values(self::$routes[count(self::$routes) - 1])[0];

         $route->layout($layouts);
         
         return $this;
     }

     /**
      * Custom route middleware
      * */
     public function middleware(array $middleware){

         $route = array_values(self::$routes[count(self::$routes) - 1])[0];

         $route->middleware($middleware);
         
         return $this;
     }

     /**
      * Add roles, permissions and attributes as the developer will have defined
      * in the AuthorizationProvider class that will determine whether the user 
      * is authorized to access this resource or not
      * */
     public function authorize(string $guard){

         $route = array_values(self::$routes[count(self::$routes) - 1])[0];

         $route->authorize($guard);
         
         return $this;
     }

     //create shared route context for two or more routes
     public static function context(){
         return new RouteContext(is_group: true);
     }

     public static function route(string $url, string $target){
         $context = new RouteContext(is_group: false);
         
         $context->url($url);
         $context->target($target);

         return $context;
     }

     public static function method(string $http_verb, ?string $method = null){
         if(!self::$context_stack){
             return;
         }

         $route_attrs = self::$context_stack[count(self::$context_stack) - 1];
         
         $route = new Route(
             $http_verb, 
             $route_attrs['url'], 
             $method ? $route_attrs['target']."@".$method : $route_attrs['target']
         );

         self::apply_context($route);

         self::$routes[] = [$route->key => $route];

         return static::instance();
     }

     public static function register_context(array $attributes) : void {

         self::$context_stack[] = $attributes;

     }

     public static function remove_context() : void {

         array_pop(self::$context_stack);
         
     }

     private static function apply_context(Route $route) : void {
         foreach(self::$context_stack as $context){

             if(!empty($context['authorize'])){
                 $route->authorize($context['authorize']);
             }

             if(!empty($context['middleware'])){
                 $route->middleware($context['middleware']); 
             }

             if(!empty($context['layout'])){
                 $route->layout($context['layout']);
             }

             if(!empty($context['scope'])){
                 $route->scope($context['scope']); 
             }

             if(!empty($context['prefix'])){
                 $route->prefix($context['prefix']); 
             }

             if(!empty($context['name'])){
                 $route->name($context['name']); 
             } 
         }
     }
}
