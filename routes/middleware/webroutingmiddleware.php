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
 * The routing middleware is responsible for the following:
 * 1. checks if the route requested is defined
 * 2. checks if the request method is valid
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Routes\Middleware;

use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Middleware\Base\BaseRoutingMiddleware;

class WebRoutingMiddleware extends BaseRoutingMiddleware{

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

     public function handle(MiddlewareRequestInterface &$request){

         //Acquire project level routes.
         $routes = $this->get_routes_from_file(DOCUMENT_ROOT.'/routes/web.php', true);
        
         //Acquire routes for all installed apps.
         foreach(INSTALLED_APPS as $app){
             $routes = array_merge($routes, $this->get_routes_from_file(DOCUMENT_ROOT.'/apps/'.$app.'/routes/web.php', true));
         }

         $routes = $this->flatten($routes);

         $this->assert_all_routes($routes);

         $this->find_and_assign_route($routes, $request);
     }
}
?>