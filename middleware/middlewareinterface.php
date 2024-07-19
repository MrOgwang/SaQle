<?php
namespace SaQle\Middleware;
interface MiddlewareInterface{
	public function next(MiddlewareInterface $middleware) : MiddlewareInterface;
	public function handle(MiddlewareRequestInterface &$request);
}
?>