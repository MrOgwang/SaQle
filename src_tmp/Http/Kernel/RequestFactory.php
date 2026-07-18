<?php

namespace SaQle\Http\Kernel;

use SaQle\Http\Request\Request;

final class RequestFactory {
	 public static function make() : Request {

	 	 $request = Request::init();

	 	 ResponseTypeResolver::resolve($request);

	 	 RequestDataBag::fill($request);

	 	 RouteMatcher::match($request);

	 	 return $request;

	 }
}