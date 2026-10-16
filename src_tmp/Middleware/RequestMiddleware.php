<?php
namespace SaQle\Middleware;

use SaQle\Http\Message\Message;

interface RequestMiddleware extends MiddlewareInterface {
	 public function before($request) : Message;
}
