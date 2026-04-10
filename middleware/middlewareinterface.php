<?php
namespace SaQle\Middleware;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\Response;

interface MiddlewareInterface {
	public function next(MiddlewareInterface $middleware) : MiddlewareInterface;
	public function handle(Request $request, ?Response $response = null);
}
