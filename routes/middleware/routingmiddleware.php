<?php
namespace SaQle\Routes\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Manager\RouteManager;
use SaQle\Permissions\Utils\PermissionUtils;
use SaQle\Core\Assert\Assert;
use SaQle\Routes\{Route, RouteGroup};

/**
* This middleware injects the found route into the request object.
*/
class RoutingMiddleware extends IMiddleware{
     use PermissionUtils;

     private array $original_web_routes = [];

     private function array_unique_trail(array $array): array{
         $unique = [];
         foreach($array as $object){
             if(!isset($unique[$object->url])){
                $unique[$object->url] = $object;
             }
         }
         return array_values($unique);
     }

     private function flatten(array $routes): array{
         $result = [];
         foreach($routes as $value){
             if(is_array($value)) {
                 $result = array_merge($result, $this->flatten($value));
             }else{
                 $result[] = $value;
             }
         }
         return $result;
     }

     private function get_routes_from_file(string $path, bool $is_web = true) : array{
         if(file_exists($path)){
             //web routes maybe grouped to define page structure, so flatten if web routes
             $routes = require_once $path;
             if($is_web){
                 $this->original_web_routes = array_merge($this->original_web_routes, $routes);
                 $routes = $this->flatten($routes);
             }
             //assert indexed array
             Assert::isList($routes, 'The file at: '.$path.' does not return an indexed array!');
             //asset array of route objects
             Assert::allIsInstanceOf($routes, Route::class, 'One or more items in routes defined in '.$path.' is not a route object!');
             return $routes;
         }

         return [];
     }

     public function handle(MiddlewareRequestInterface &$request){

         //Acquire project level routes.
         $api_routes = $this->get_routes_from_file(DOCUMENT_ROOT.'/routes/api.php', false);
         $web_routes = $this->get_routes_from_file(DOCUMENT_ROOT.'/routes/web.php', true);
        
         //Acquire routes for all installed apps.
         foreach(INSTALLED_APPS as $app){
             $api_routes = array_merge($api_routes, $this->get_routes_from_file(DOCUMENT_ROOT.'/apps/'.$app.'/routes/api.php', false));
             $web_routes = array_merge($web_routes, $this->get_routes_from_file(DOCUMENT_ROOT.'/apps/'.$app.'/routes/web.php', true));
         }

         //get a matching route
         $match = null;
         foreach(array_merge($web_routes, $api_routes) as $r){
             if($r->matches()){
                 $match = $r;
                 break;
             }
         }

         if(!$match){
            throw new RouteNotFoundException();
         }

         //resolve target for matching route
         $target = $match->get_target();
         if(is_callable($target)){
             $match->set_target($target());
         }

         $request->route = $match;

         $request->trail = $this->find_trail($this->original_web_routes, $match->get_url(), []);

         //print_r($request->trail);
         //print_r($request->user);
        
     	 parent::handle($request);
     }

     private function extract_route_trail($route){
         $target = $route->get_target();
         if(is_callable($target)){
             $target = $target();
         }
         return (Object)['url' => $route->get_url(), 'target' => $target];
     }

     private function find_trail(array $array, string $url, array $trail = []){
         $r = $this->find_element($array, $url);
         if($r[1])
             return $this->array_unique_trail(array_merge($trail, $r));

         $r = array_filter($r, function($v){return !is_null($v);});

         $t = [];
         foreach($array as $_i => $a){
             if(is_array($a)){
                 $is_contained = $this->contained_in_nested($a, $url);
                 if($is_contained){
                     $t = $this->find_trail($a, $url, $trail);
                     break;
                 }
             }
         }

         return $this->array_unique_trail(array_merge($r, $t));
     }

     private function contained_in_nested(array $array, string $url){
         $array = $this->flatten($array);
         $found = array_filter($array, function($item) use ($url){
             return $item->get_url() === $url;
         });

         return !empty($found);
     }

     private function find_element(array $array, string $url){
         $result = [null, null];
         foreach($array as $a){
             if(!is_array($a) && $a->get_url() === $url){
                $result[0] = $this->extract_route_trail($array[0]);
                $result[1] = $this->extract_route_trail($a);
             }
         }

         if(!$result[0] && !$result[1])
            $result[0] = $this->extract_route_trail($array[0]);

         return $result;
     }
}
?>