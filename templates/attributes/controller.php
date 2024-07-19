<?php
declare(strict_types = 0);
namespace SaQle\Templates\Attributes;

#[Attribute(Attribute::TARGET_FUNCTION, Attribute::IS_REPEATABLE)]
class Controller{
	 private string $controller;
	 public function __construct(string $controller){
	 	$this->controller = $controller;
	 }
}
?>