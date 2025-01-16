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
 * 3. determine whether the route selected requires permissions or not
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Routes\Middleware;

use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Routes\Middleware\Base\BaseRoutingMiddleware;
use SaQle\Http\Response\{HttpMessage, StatusCode};
use SaQle\Http\Request\Processors\ApiRequestProcessor;
use SaQle\Routes\Exceptions\{RouteNotFoundException, MethodNotAllowedException};

class ApiRoutingMiddleware extends BaseRoutingMiddleware{

     public function handle(MiddlewareRequestInterface &$request){
         try{
             //Acquire project level routes.
             $routes = $this->get_routes_from_file(DOCUMENT_ROOT.'/routes/api.php', true);
        
             //Acquire routes for all installed apps.
             foreach(INSTALLED_APPS as $app){
                 $routes = array_merge($routes, $this->get_routes_from_file(DOCUMENT_ROOT.'/apps/'.$app.'/routes/api.php', true));
             }

             $this->assert_all_routes($routes);

             $this->find_and_assign_route($routes, $request);

             $controller = explode("@", $request->route->get_target())[0];

             $permissions = (new $controller())->get_permissions();

             if($permissions){
                 $request->enforce_permissions = true;
             }
         }catch(RouteNotFoundException $e){
             (new ApiRequestProcessor())->process(
                new HttpMessage(code: StatusCode::NOT_FOUND, message: $e->get_message())
             );
         }catch(MethodNotAllowedException $e){
             (new ApiRequestProcessor())->process(
                new HttpMessage(code: StatusCode::METHOD_NOT_ALLOWED, message: $e->get_message())
             );
         }catch(\Exception $e){
             (new ApiRequestProcessor())->process(
                new HttpMessage(code: StatusCode::INTERNAL_SERVER_ERROR, message: $e->getMessage())
             );
         }
     }
}
?>