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
 * The route layout middleware is responsible for layout out the structure of
 * a web app depending on which route was matched
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Routes\Middleware;

use SaQle\Routes\Middleware\Base\BaseRoutingMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;

class RouteLayoutMiddleware extends BaseRoutingMiddleware{

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

     private function find_trail(array $array, string $url, array $params, array $trail = []){
         $r = $this->find_element($array, $url, $params);
         if($r[1]){
             return $this->array_unique_trail(array_merge($trail, $r));
         }

         $r = array_filter($r, function($v){return !is_null($v);});

         $t = [];
         foreach($array as $_i => $a){
             if(is_array($a)){
                 $is_contained = $this->contained_in_nested($a, $url);
                 if($is_contained){
                     $t = $this->find_trail($a, $url, $params, $trail);
                     break;
                 }
             }
         }

         return $this->array_unique_trail(array_merge($r, $t));
     }

     private function find_element(array $array, string $url, array $params){
         $result = [null, null];
         foreach($array as $a){
             if(!is_array($a) && rtrim($a->get_url(), "/") === $url){
                $result[0] = $this->extract_route_trail($array[0], $params);
                $result[1] = $this->extract_route_trail($a, $params);
             }
         }

         if(!$result[0] && !$result[1])
            $result[0] = $this->extract_route_trail($array[0], $params);

         return $result;
     }

     private function extract_route_trail($route, $params){
         $target = $route->get_target();
         if(is_callable($target)){
             $target = $target($params);
         }
         return (Object)['url' => $route->get_url(), 'target' => $target];
     }

     private function contained_in_nested(array $array, string $url){
         $array = $this->flatten($array);
         $found = array_filter($array, function($item) use ($url){
             return $item->get_url() === $url;
         });

         return !empty($found);
     }

     private function array_unique_trail(array $array): array{
         $unique = [];
         foreach($array as $object){
             if(!isset($unique[$object->url])){
                $unique[$object->url] = $object;
             }
         }
         return array_values($unique);
     }

     public function handle(MiddlewareRequestInterface &$request){
         //Acquire project level routes.
         $routes = $this->get_routes_from_file(DOCUMENT_ROOT.'/routes/web.php', true);
        
         //Acquire routes for all installed apps.
         foreach(INSTALLED_APPS as $app){
             $routes = array_merge($routes, $this->get_routes_from_file(DOCUMENT_ROOT.'/apps/'.$app.'/routes/web.php', true));
         }

         $request->trail = $this->find_trail($routes, $request->route->get_url(), $request->route->get_params(), []);

     	 parent::handle($request);
     }
}
?>