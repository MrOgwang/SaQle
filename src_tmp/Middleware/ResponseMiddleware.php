<?php
namespace SaQle\Middleware;

use SaQle\Http\Message\Message;

interface ResponseMiddleware extends MiddlewareInterface {
	 public function after($request, $response) : Message;
}
