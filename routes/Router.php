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

class Router {
     protected static array $routes = [];
     
     /**
      * Define a route for a get request
      * 
      * @param string $url 
      *     - the url to match for this route
      * 
      * @param string | array $target
      *     - this is the controller class name or the view name if given as string.
      *     - if this is provided as an array, the array must be a key => value array
      *       where the key is a user role and the value is a controller class name or view name
      * 
      * @param nullable string $target_method:
      *     - this is the name of the method on the controller to call. Only provided if the target is a controller.
      *     - Where this is not provided for a controller, the $target_method defaults the name of the http method in all lowercase.
      * */
     static public function get(string $url, string | array $target, ?string $target_method = null) : Route {
         return self::register_route(['get' => $target_method ?? 'get'], $url, $target, $target_method);
     }

     /**
      * Define a route for a post request
      * 
      * @param string $url 
      *     - the url to match for this route
      * 
      * @param string | array $target
      *     - this is the controller class name or the view name if given as string.
      *     - if this is provided as an array, the array must be a key => value array
      *       where the key is a user role and the value is a controller class name or view name
      * 
      * @param nullable string $target_method:
      *     - this is the name of the method on the controller to call. Only provided if the target is a controller.
      *     - Where this is not provided for a controller, the $target_method defaults the name of the http method in all lowercase.
      * */
     static public function post(string $url, string | array $target, ?string $target_method = null) : Route {
         return self::register_route(['post' => $target_method ?? 'post'], $url, $target, $target_method);
     }

     /**
      * Define route for a patch request
      * 
      * @param string $url 
      *     - the url to match for this route
      * 
      * @param string | array $target
      *     - this is the controller class name or the view name if given as string.
      *     - if this is provided as an array, the array must be a key => value array
      *       where the key is a user role and the value is a controller class name or view name
      * 
      * @param nullable string $target_method:
      *     - this is the name of the method on the controller to call. Only provided if the target is a controller.
      *     - Where this is not provided for a controller, the $target_method defaults the name of the http method in all lowercase.
      * */
     static public function patch(string $url, string | array $target, ?string $target_method = null) : Route {
         return self::register_route(['patch' => $target_method ?? 'patch'], $url, $target, $target_method);
     }

     /**
      * Define route for a put request
      * 
      * @param string $url 
      *     - the url to match for this route
      * 
      * @param string | array $target
      *     - this is the controller class name or the view name if given as string.
      *     - if this is provided as an array, the array must be a key => value array
      *       where the key is a user role and the value is a controller class name or view name
      * 
      * @param nullable string $target_method:
      *     - this is the name of the method on the controller to call. Only provided if the target is a controller.
      *     - Where this is not provided for a controller, the $target_method defaults the name of the http method in all lowercase.
      * */
     static public function put(string $url, string | array $target, ?string $target_method = null) : Route {
         return self::register_route(['put' => $target_method ?? 'put'], $url, $target, $target_method);
     }

     /**
      * Define route for a delete request
      * 
      * @param string $url 
      *     - the url to match for this route
      * 
      * @param string | array $target
      *     - this is the controller class name or the view name if given as string.
      *     - if this is provided as an array, the array must be a key => value array
      *       where the key is a user role and the value is a controller class name or view name
      * 
      * @param nullable string $target_method:
      *     - this is the name of the method on the controller to call. Only provided if the target is a controller.
      *     - Where this is not provided for a controller, the $target_method defaults the name of the http method in all lowercase.
      * */
     static public function delete(string $url, string | array $target, ?string $target_method = null) : Route {
         return self::register_route(['delete' => $target_method ?? 'delete'], $url, $target, $target_method);
     }

     /**
      * Define route for a more than one http method
      * 
      * @param array $methods
      *     - a key => value array of the http methods to match,
      *       where they key is the http method name and the value is the 
      *       controller method name.
      * 
      * @param string $url 
      *     - the url to match for this route
      * 
      * @param string | array $target
      *     - this is the controller class name or the view name if given as string.
      *     - if this is provided as an array, the array must be a key => value array
      *       where the key is a user role and the value is a controller class name or view name
      * 
      * */
     static public function match(array $methods, string $url, string | array $target) : Route {
         $clean_methods = [];
         foreach($methods as $k => $v){
             $m = is_numeric($k) ? $v : $k;
             $t = is_numeric($k) ? strtolower($v) : $v;

             $clean_methods[$m] = $t;
         }
         return self::register_route($clean_methods, $url, $target);
     }

     static public function from_parents(array $parents, array $routes){
         foreach($routes as $r){
             $r->with_parents($parents);
         }
     }

     /**
      * Register a given route 
      * */
     static private function register_route(array $methods, string $url, null | string | array $target, ?string $target_method = null) : Route {
         $route = new Route($url, $target, $methods);
         self::$routes[] = $route;
         return $route;
     }

     /**
      * Register a given redirect route 
      * */
     static private function register_redirect_route(array $methods, string $from_url, string $to_url) : Route {
         $route = new Route($from_url, '', $methods);
         $route->redirect = true;
         $route->redirect_url = $to_url;
         self::$routes[] = $route;
         return $route;
     }

     public static function all(): array {
         return self::$routes;
     }

     public static function clear(): void {
         self::$routes = [];
     }

     public static function redirect(string $from_url, string $to_url){
         return self::register_redirect_route(['get' => 'get'], $from_url, $to_url);
     }
}
