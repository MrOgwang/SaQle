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

use SaQle\Core\Assert\Assert;

final class Router {

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
      * The routes are batched(so this is technically an array of arrays). This is to make it easy
      * to apply shared route properties to a group of routes.
      * */
     protected static array $routes = [];
     
     /**
      * Define a route for a get request
      * 
      * @param string $url 
      *     - the url to match for this route
      * 
      * @param string $target 
      * 
      * Target can be provided in several formats:
      * 
      * ControllerName@method - the controller class name and the method to execute
      * componentname@method  - the component name and the method to execute
      * ControllerName        - just the controller name, the method to execute will be determined
      * componentname         - just the component name, the method to excute will be determined automatically if a component has a controller
      * 
      * */
     static public function get(string $url, string $target) : Router {
         $route = new Route('get', $url, $target);
         self::$routes[] = [$route];

         return static::instance();
     }

     //parameters as descirbed in get
     static public function post(string $url, string $target) : Router {
         $route = new Route('post', $url, $target);
         self::$routes[] = [$route];

         return static::instance();
     }

     //parameters as descirbed in get
     static public function patch(string $url, string $target) : Router {
         $route = new Route('patch', $url, $target);
         self::$routes[] = [$route];

         return static::instance();
     }

     //parameters as descirbed in get
     static public function put(string $url, string $target) : Router {
         $route = new Route('put', $url, $target);
         self::$routes[] = [$route];

         return static::instance();
     }

     //parameters as descirbed in get
     static public function delete(string $url, string $target) : Router {
         $route = new Route('delete', $url, $target);
         self::$routes[] = [$route];

         return static::instance();
     }

     /**
      * Handle more than one http verb for a given route and target
      * 
      * @param array $methods
      *    - http methods to handle
      * 
      * @param string $url 
      *     - the url
      * 
      * @param string $target
      * 
      * Target can be provided in several formats:
      * 
      * ControllerName@method - the controller class name and the method to execute
      * componentname@method  - the component name and the method to execute
      * ControllerName        - just the controller name, the method to execute will be determined
      * componentname         - just the component name, the method to excute will be determined automatically if a component has a controller
      * 
      * */
     static public function match(array $methods, string $url, string $target) : Router {
         $routes = [];
         foreach($methods as $m){
             $routes[] = new Route($m, $url, $target);
         }

         self::$routes[] = $routes;

         return static::instance();
     }

     public static function all(): array {
         //return a flattened array
         return array_merge(...self::$routes);
     }

     public static function clear(): void {
         self::$routes = [];
     }

     // Route decoration methods

     private static function apply_decoration(string $deco, ...$params){
         if(!self::$routes)
             return;

         $last_batch = self::$routes[ count(self::$routes) - 1];

         foreach($last_batch as $r){
             match($deco){
                 'compose_with' => $r->compose_with(...$params),
                 'requires'     => $r->requires(...$params),
                 'requires_any' => $r->requires_any(...$params),
                 'requires_all' => $r->requires_all(...$params),
                 'respond_with' => $r->respond_with(...$params)
             };
         }
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
     public function compose_with(array $layouts, ?string $resolver = null){
         $this->apply_decoration('compose_with', ...['layouts' => $layouts, 'resolver' => $resolver]);
         return $this;
     }

     /**
      * Add roles, permissions and attributes as the developer will have defined
      * in the AuthorizationProvider class that will determine whether the user 
      * is authorized to access this route or not
      * */
     public function requires(string $guard){
         $this->apply_decoration('requires', ...['guard' => $guard]);
         return $this;
     }

     public function requires_any(array $guards){
         $this->apply_decoration('requires_any', ...['guards' => $guards]);
         return $this;
     }

     public static function requires_all(array $guards){
         $this->apply_decoration('requires_all', ...['guards' => $guards]);
         return $this;
     }

     /**
      * Add url aliases for this route
      * */
     public static function with_aliase(array $aliases){
         if(!self::$routes)
             return $this;

         //aliases must be an array of non empty strings, otherwise complain loudly
         Assert::allStringNotEmpty($aliases, 'Please provide an array of non empty string names for url aliases');

         $last_batch = self::$routes[ count(self::$routes) - 1];
         $new_last_batch = [];

         //for each aliase, for each route in last batch, create a new route 
         foreach($last_batch as $r){
             foreach($aliases as $a){
                 $aliase_route = clone $r;
                 $aliase_route->url = $a;
                 $new_last_batch[] = $aliase_route;
             }
         }

         self::$routes[] = $new_last_batch;

         return $this;
     }

     /**
      * Set the response type from this route
      * */
     public function respond_with(array $restype){
         $this->apply_decoration('respond_with', ...['restype' => $restype]);
         return $this;
     }
}
