<?php
namespace SaQle\Routes\Middleware\Interface;

interface IRoutingMiddleware{
	 public function get_routes_from_file(string $path) : array;
}
?>