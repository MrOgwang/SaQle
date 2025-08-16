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
 * The base routing middleware
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Routes\Middleware\Base;

use SaQle\Routes\Middleware\Interface\IRoutingMiddleware;
use SaQle\Core\Assert\Assert;
use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Route;
use SaQle\Routes\Exceptions\{RouteNotFoundException, MethodNotAllowedException};

abstract class BaseRoutingMiddleware extends IMiddleware implements IRoutingMiddleware{

     abstract public function find_and_assign_route(MiddlewareRequestInterface &$request, mixed $routes) : void;
     
     public function load_routes(string $type = 'web') : mixed {
         //load project level routes.
         $project_path = DOCUMENT_ROOT.'/routes/'.$type.'.php';
         if(file_exists($project_path)){
             require_once $project_path;
         }
    
         //load routes for all installed apps.
         foreach(INSTALLED_APPS as $app){
             $path = DOCUMENT_ROOT.'/apps/'.$app.'/routes/'.$type.'.php';
             if(file_exists($path)){
                 require $path;
             }
         }

         return null;
     }

     protected function assert_all_routes(array $routes) : void{
         //asset array of route objects
         Assert::allIsInstanceOf($routes, Route::class, 'One or more items in routes is not a route object!');
     }

     private function return_matching_route($match, $matches){
         if(!$match){ //a match wasn't found
             throw new RouteNotFoundException(url: $_SERVER['REQUEST_URI']);
         }

         if(!$matches[1]){ //a match was found with the wrong method
             throw new MethodNotAllowedException(url: $_SERVER['REQUEST_URI'], method: $_SERVER['REQUEST_METHOD'], methods: $match->methods);
         }

         return $match;
     }

     protected function find_matching_route(array $routes, $request){
         //find all the urls matching current one
         [$matching_routes, $match_results] = $this->find_matching_routes($routes, $request);

         if(count($matching_routes) === 1)
             return $this->return_matching_route($matching_routes[0], $match_results[0]);


         //get a matching route
         $match = null;
         $matches = [false, false];
         foreach($matching_routes as $i => $r){
             if($match_results[$i][0] === true && $match_results[$i][1] === true){
                 $match = $r;
                 $matches = [true, true];
                 break;
             }
         }

         return $this->return_matching_route($match, $matches);
     }

     private function find_matching_routes(array $routes, $request){
         $matching_routes = [];
         $match_results   = [];

         foreach($routes as $r){
             $matches = $r->matches();
             if($matches[0] === true){
                 $matching_routes[] = $r;
                 $match_results[] = $matches;
             }
         }

         //filter by permissions
         $allowed_matches = [];
         $allowed_matches_results = [];
         foreach($matching_routes as $i => $mr){
             if(is_null($mr->perm) || !is_callable($mr->perm)){
                 $allowed_matches[] = $mr;
                 $allowed_matches_results[] = $match_results[$i];
                 continue;
             }

             $permission_callback = $mr->perm;
             $result = $permission_callback($request);
             if($result){
                 $allowed_matches[] = $mr;
                 $allowed_matches_results[] = $match_results[$i];
             }
         }

         return [$allowed_matches, $allowed_matches_results];
     }
}
