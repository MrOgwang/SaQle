<?php
namespace SaQle\Services\Container;

class Cf{
	public static function create($name){
		 $container = require DI_CONTAINER;
	 	 return $container->get($name);
	}
}
?>