<?php
namespace SaQle\Middleware;

use SaQle\Http\Response\Message;

interface RequestMiddleware extends MiddlewareInterface {
	 public function before($request) : ? Message;
}
