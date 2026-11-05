<?php

namespace SaQle\Http\Kernel;

use SaQle\Http\Request\Request;
use SaQle\Routing\MatchedRoute;
use SaQle\Core\Exceptions\Route\RouteNotFoundException;

final class RequestFactory {

	 private static function assign_route(Request $request, ?MatchedRoute $route = null){

	 	 if(!$route){

             $url = $request->uri();
             $method = $request->method();

             throw new RouteNotFoundException(
                 "The resource [{$method}:{$url}] either does not exist or has been permanently moved!",
                 ['url' => $url, 'method' => $method]
             );
         }

         $request->route = $route;
         
         //set path params
         foreach($route->params as $pk => $pv){
             $request->add_path_param($pk, $pv);
         }

         //set query params
         foreach($route->queries as $qk => $qv){
             $request->add_query_param($qk, $qv);
         }
	 }

	 public static function make() : Request {

	 	 $request = Request::init();

	 	 $route = RouteMatcher::match($request->uri(), $request->method());

	 	 self::assign_route($request, $route);

	 	 ResponseTypeResolver::resolve($request);

	 	 RequestDataBag::fill($request);

	 	 return $request;
	 }
}