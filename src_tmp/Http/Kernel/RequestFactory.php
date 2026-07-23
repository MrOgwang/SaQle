<?php

namespace SaQle\Http\Kernel;

use SaQle\Http\Request\Request;

final class RequestFactory {
	 public static function make() : Request {

	 	 $request = Request::init();

	 	 RouteMatcher::match($request);

	 	 ResponseTypeResolver::resolve($request);

	 	 RequestDataBag::fill($request);

	 	 return $request;
	 }
}