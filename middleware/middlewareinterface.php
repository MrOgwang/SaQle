<?php
namespace SaQle\Middleware;

use SaQle\Http\Response\HttpMessage;

interface MiddlewareInterface {
	 public function handle($request, $response = null) : ?HttpMessage;
}
