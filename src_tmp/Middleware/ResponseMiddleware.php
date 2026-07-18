<?php
namespace SaQle\Middleware;

use SaQle\Http\Response\Message;

interface ResponseMiddleware extends MiddlewareInterface {
	 public function after($request, $response) : ? Message;
}
