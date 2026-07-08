<?php
namespace SaQle\Middleware;

use SaQle\Http\Response\Message;

interface MiddlewareInterface {
	 public function handle($request, $response = null) : ?Message;
}
