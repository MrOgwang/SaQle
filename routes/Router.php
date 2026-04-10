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
use SaQle\Core\Registries\RouteRegistry;
use SaQle\Http\Request\RequestScope;
use SaQle\Http\Response\ResponseType;
use RuntimeException;

final class Router {

     protected static array $group_stack = [];

     protected static array $route_names = [];

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
     static public function get(string $url, string $target, ?string $model_class = null) : Router {
         $route = new Route('get', $url, $target, $model_class);

         self::$routes[] = [$route];

         self::push_group_attributes();

         return static::instance();
     }

     //parameters as descirbed in get
     static public function post(string $url, string $target, ?string $model_class = null) : Router {
         $route = new Route('post', $url, $target, $model_class);

         self::$routes[] = [$route];

         self::push_group_attributes();

         return static::instance();
     }

     //parameters as descirbed in get
     static public function patch(string $url, string $target, ?string $model_class = null) : Router {
         $route = new Route('patch', $url, $target, $model_class);

         self::$routes[] = [$route];

         self::push_group_attributes();

         return static::instance();
     }

     //parameters as descirbed in get
     static public function put(string $url, string $target, ?string $model_class = null) : Router {
         $route = new Route('put', $url, $target, $model_class);

         self::$routes[] = [$route];

         self::push_group_attributes();

         return static::instance();
     }

     //parameters as descirbed in get
     static public function delete(string $url, string $target, ?string $model_class = null) : Router {
         $route = new Route('delete', $url, $target, $model_class);

         self::$routes[] = [$route];

         self::push_group_attributes();

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
     static public function match(array $methods, string $url, string $target, ?string $model_class = null) : Router {
         $routes = [];
         foreach($methods as $m){
             $routes[] = new Route($m, $url, $target, $model_class);
         }

         self::$routes[] = $routes;

         self::push_group_attributes();

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

         if($deco === 'name'){
             foreach($last_batch as $r_index => $r){
                 $name = $params['name'][$r_index] ?? null;
                 if($name && !in_array($name, self::$route_names)){
                     $r->set_name($name);
                     self::$route_names[] = $name;
                 }else{
                    throw new RuntimeException("No name defined for the route - {$r->method} : {$r->url}");
                 }
             }
         }else{
             foreach($last_batch as $r){
                 match($deco){
                     'compose_with' => $r->compose_with(...$params),
                     'requires'     => $r->requires(...$params),
                     'requires_any' => $r->requires_any(...$params),
                     'requires_all' => $r->requires_all(...$params),
                     'respond_with' => $r->respond_with(...$params),
                     'sse'          => $r->sse(...$params),
                     'scope'        => $r->scope(...$params)
                 };
             }
         }
     }

     /**
      * Provide a custom route name.
      * 
      * @var string name - name of route
      * */
     public function name(array | string $name){
         if(!is_array($name)){
             $name = [$name];
         }

         $this->apply_decoration('name', ...['name' => $name]);
         return $this;
     }

     /**
      * Provide a route scope
      * 
      * @var RequestScope $scope
      * */
     public function scope(RequestScope $scope){
         $this->apply_decoration('scope', ...['scope' => $scope]);
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
         $this->apply_decoration('sse', ...['event' => $event, 'interval' => $interval]);
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
     public function compose_with(array $layouts){
         $this->apply_decoration('compose_with', ...['layouts' => $layouts]);
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

     public function requires_all(array $guards){
         $this->apply_decoration('requires_all', ...['guards' => $guards]);
         return $this;
     }

     /**
      * Add url aliases for this route
      * */
     public function with_aliase(array $aliases){
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
     public function respond_with(ResponseType $restype){
         $this->apply_decoration('respond_with', ...['restype' => $restype]);
         return $this;
     }

     protected static function with_group(array $attributes, callable $routes): void {
         self::$group_stack[] = $attributes;

         $routes();
         
         array_pop(self::$group_stack);
     }

     private static function push_group_attributes(){
         if(!self::$group_stack) return;

         $router = static::instance();

         //Apply group attributes
         foreach (self::$group_stack as $group){
             if(!empty($group['guards_all'])) {
                 $router->requires_all($group['guards_all']);
             }

             if (!empty($group['guards_any'])) {
                 $router->requires_any($group['guards_any']);
             }

             if (!empty($group['restype'])) {
                 $router->respond_with($group['restype']);
             }

             if (!empty($group['layouts'])) {
                 $router->compose_with($group['layouts']);
             }

             if (!empty($group['scope'])) {
                 $router->scope($group['scope']);
             }
         }
     }

     public static function with_layout(array $layouts, callable $routes): void {
         self::with_group(['layouts' => $layouts], $routes);
     }

     public static function with_guards(array $guards, callable $routes, string $mode = 'all'): void {
         self::with_group([$mode === 'all' ? 'guards_all' : 'guards_any' => $guards], $routes);
     }

     public static function with_response(ResponseType $type, callable $routes): void {
         self::with_group(['restype' => $type], $routes);
     }

     public static function with_scope(RequestScope $scope, callable $routes): void {
         self::with_group(['scope' => $scope], $routes);
     }

     public static function find_matching_route(string $method, string $uri): ?array{
        // Merge all prefixes
        $all_prefixes = array_merge(config('app.api_url_prefixes'), config('app.sse_url_prefixes'));

        //Get all compiled routes from your registry
        $compiled_routes = RouteRegistry::all();

        // Parse URI
        $parts = parse_url($uri);
        $path = '/' . trim($parts['path'] ?? '/', '/'); // normalize path with leading slash
        $query_params = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query_params);
        }

        // Strip prefix
        $prefix_data = self::strip_prefix($path, $all_prefixes);
        $prefix = $prefix_data['prefix'];           // matched prefix string (e.g., /api/v1)
        $path_without_prefix = $prefix_data['path']; // path after removing prefix

        // Iterate over compiled routes
        foreach ($compiled_routes as $route) {
            // HTTP method check
            if (strtoupper($route['method']) !== strtoupper($method)) continue;

            // Match path regex
            if (preg_match($route['pattern'], $path_without_prefix, $matches)) {
                // Extract path params
                $params = [];
                if (!empty($route['param_names'])) {
                    foreach ($route['param_names'] as $i => $name) {
                        $params[$name] = $matches[$i + 1] ?? null;
                    }
                }

                return [
                    'route' => $route['route'],
                    'params' => $params,
                    'query' => $query_params,
                    'path' => $path_without_prefix,
                    'method' => $method,
                    'prefix' => $prefix,
                ];
            }
        }

        // No match found
        return null;
     }

     /**
     * Strip the longest matching prefix from the path
     */
     protected static function strip_prefix(string $path, array $prefixes): array{
        // Normalize path: ensure leading slash
        $normalizedPath = '/' . trim($path, '/');

        // Sort prefixes by length descending to match the longest first
        usort($prefixes, fn($a, $b) => strlen(trim($b, '/')) - strlen(trim($a, '/')));

        foreach ($prefixes as $prefix) {
            $normalizedPrefix = '/' . trim($prefix, '/');
            if (str_starts_with($normalizedPath, $normalizedPrefix)) {
                $stripped = substr($normalizedPath, strlen($normalizedPrefix));
                $stripped = '/' . ltrim($stripped, '/'); // ensure leading slash
                return ['prefix' => $normalizedPrefix, 'path' => $stripped];
            }
        }

        return ['prefix' => null, 'path' => $normalizedPath];
     }

     /**
     * Helper: check if normalized prefix is in an array of prefixes
     */
     protected static function prefix_in_array(string $prefix, array $prefix_array): bool{
        foreach ($prefix_array as $p) {
            if ('/' . trim($p, '/') === $prefix) return true;
        }
        return false;
     }
}
