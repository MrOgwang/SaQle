<?php
declare(strict_types = 1);
namespace SaQle\Routes;

use Closure;

class Router{
	 /**
	  * Register a post route
	  * @param string url: the url 
	  * @param array $target:
	  * @param array $permissions: an array of permission classes provded in any of two formats below:
	  *     1. [permissionClassOne, permissionClassTwo]
	  *     2. [['permissionClassOne' => [arg1, arg2]], permissionClassTwo]
	  * */
	 static public function post(string $url, array $target, array $permissions = []){
		 return new Route(['POST'], $url, $target, $permissions);
	 }
	 static public function get(string $url, array $target, array $permissions = []){
		 return new Route(['GET'], $url, $target, $permissions);
	 }
	 static public function put(string $url, array $target, array $permissions = []){
		 return new Route(['PUT'], $url, $target, $permissions);
	 }
	 static public function patch(string $url, array $target, array $permissions = []){
		 return new Route('PATCH', $url, $target, $permissions);
	 }
	 static public function allow(array $methods, $url, array $target, array $permissions = []){
	 	 return new Route($methods, $url, $target, $permissions);
	 }
}
?>