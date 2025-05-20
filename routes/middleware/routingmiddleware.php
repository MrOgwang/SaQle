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
use SaQle\Middleware\IMiddleware;

class RoutingMiddleware extends IMiddleware{
     
     public function handle(MiddlewareRequestInterface &$request){

         if($request->is_api_request()){
             (new ApiRoutingMiddleware())->handle($request);
         }else{
             (new WebRoutingMiddleware())->handle($request);
         }

     	 parent::handle($request);
     }

}
