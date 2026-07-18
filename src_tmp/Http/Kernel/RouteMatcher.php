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
 * The route matcher finds a matching route for an incoming request
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

declare(strict_types = 1);

namespace SaQle\Http\Kernel;

use SaQle\Core\Registries\RouteRegistry;
use SaQle\Core\Support\RouteResolver;
use SaQle\Http\Request\Request;
use SaQle\Routes\MatchedRoute;
use SaQle\Core\Exceptions\Route\RouteNotFoundException;
use SaQle\Core\Ui\UiComponentDefinition;
use SaQle\Http\Request\RequestScope;
use RuntimeException;

final class RouteMatcher {

     public static function match(Request $request){
         //find matching route
         $match = self::find_matching_route($request->method(), $request->uri());
         
         if(!$match){
             $url = $request->uri();
             throw new RouteNotFoundException(
                 "The resource [".$url."] either does not exist or has been permanently moved!",
                 ['url' => $url]
             );
         }

         //set request route
         $matched_route = new MatchedRoute(
             $match['route']['url'],
             $match['path'], 
             $match['method'], 
             UiComponentDefinition::from_array($match['route']['compiled_target']),
             $match['route']['name'],
             RequestScope::from($match['route']['scope']),
             $match['route']['model_class'],
             $match['route']['layout'],
             $match['route']['guards'],
             $match['prefix'],
             $match['route']['sse_event'] ?? null
         );

         $request->route = $matched_route;
         
         //set path params
         foreach($match['params'] as $pk => $pv){
             $request->add_path_param($pk, $pv);
         }

         //set query params
         foreach($match['query'] as $qk => $qv){
             $request->add_query_param($qk, $qv);
         }

     }

     private static function find_matching_route(string $method, string $uri): ?array{

         //merge all prefixes
         $all_prefixes = array_merge(config('app.api_url_prefixes'), config('app.sse_url_prefixes'));

         //get all compiled routes from your registry
         $compiled_routes = RouteRegistry::all();

         //parse URI
         $parts = parse_url($uri);
         $path = '/'.trim($parts['path'] ?? '/', '/'); // normalize path with leading slash
         $query_params = [];
         if(isset($parts['query'])){
             parse_str($parts['query'], $query_params);
         }

         //strip prefix
         $prefix_data = self::strip_prefix($path, $all_prefixes);
         $prefix = $prefix_data['prefix'];           // matched prefix string (e.g., /api/v1)
         $path_without_prefix = $prefix_data['path']; // path after removing prefix

         //iterate over compiled routes
         foreach($compiled_routes as $route){
             //HTTP method check
             if(strtoupper($route['method']) !== strtoupper($method)) continue;

             //match path regex
             if(preg_match($route['pattern'], $path_without_prefix, $matches)){

                 //Extract path params
                 $params = [];
                 if(!empty($route['param_names'])) {
                     foreach ($route['param_names'] as $i => $name) {
                         $params[$name] = $matches[$i + 1] ?? null;
                     }
                 }

                 if($route['type'] === 'conditional'){

                     $resolver_class = $route['resolver'];
                     if(!is_a($resolver_class, RouteResolver::class, true)){
                         throw new RuntimeException("The class {$resolver_class} must be an instance of a ".RouteResolver::class);
                     }

                     $resolver_instance = resolve($resolver_class);
                     $route_details = $route['variants'][$resolver_instance->resolve(request())] ?? null;

                     if(!$route_details){
                         throw new RuntimeException("Invalid route resolver class definition for: {$resolver_class}!");
                     }

                 }else{
                     $route_details = $route['route'];
                 }

                 return [
                     'route' => $route_details,
                     'params' => $params,
                     'query' => $query_params,
                     'path' => $path_without_prefix,
                     'method' => $method,
                     'prefix' => $prefix,
                 ];
            }
         }

         //no match found
         return null;
     }

     //strip the longest matching prefix from the path
     protected static function strip_prefix(string $path, array $prefixes): array{
         //normalize path: ensure leading slash
         $normalized_path = '/' . trim($path, '/');

         //sort prefixes by length descending to match the longest first
         usort($prefixes, fn($a, $b) => strlen(trim($b, '/')) - strlen(trim($a, '/')));

         foreach($prefixes as $prefix){
             $normalized_prefix = '/' . trim($prefix, '/');
             if(str_starts_with($normalized_path, $normalized_prefix)){
                 $stripped = substr($normalized_path, strlen($normalized_prefix));
                 $stripped = '/' . ltrim($stripped, '/'); // ensure leading slash
                 return ['prefix' => $normalized_prefix, 'path' => $stripped];
             }
         }

         return ['prefix' => null, 'path' => $normalized_path];
     }

     //check if normalized prefix is in an array of prefixes
     protected static function prefix_in_array(string $prefix, array $prefix_array) : bool {
         foreach($prefix_array as $p) {
             if('/'.trim($p, '/') === $prefix) return true;
         }

         return false;
     }
}
