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
use SaQle\Routes\{Router, MatchedRoute};
use SaQle\Core\Exceptions\Route\RouteNotFoundException;
use SaQle\Core\Exceptions\Http\NotAcceptableException;

class RoutingMiddleware extends IMiddleware{
     
     public function handle(MiddlewareRequestInterface $request){
         //find matching route
         $match = Router::find_matching_route($request->method(), $request->uri());

         if (!$match) throw new RouteNotFoundException(['url' => $request->uri()]);

         //set request route
         $matched_route = new MatchedRoute(
             $match['route']['url'],
             $match['path'], 
             $match['method'], 
             $match['route']['compiled_target'],
             $match['route']['model_class'],
             $match['route']['layout'],
             $match['route']['guards'],
             $match['route']['restype'],
             $match['route']['trail'],
             $match['prefix']
         );

         $request->route = $matched_route;

         //check that route supports response type
         $response_type = match($request->intent->value){
             'api', 'ajax' => 'json',
             'web'         => 'html',
             'sse'         => 'sse'
         };

         if(!$request->route->supports($response_type)){
             throw new NotAcceptableException('The route '.$request->route->url.' does not support [ '.$response_type.' ] responses!');
         }

         //set path params
         foreach($match['params'] as $pk => $pv){
             $request->add_path_param($pk, $pv);
         }

         //set query params
         foreach($match['query'] as $qk => $qv){
             $request->add_query_param($qk, $qv);
         }

     	 parent::handle($request);
     }
}
