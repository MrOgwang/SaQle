<?php
declare(strict_types = 0);
namespace SaQle\Templates\Attributes;

#[Attribute(Attribute::TARGET_FUNCTION)]
class Template{
	 private array  $context = [];
	 private bool   $is_toplevel = false;
	 private array  $default_child = [];
	 private array  $children = [];

	 public function __construct(array $context = [], bool $is_toplevel = false, array $default_child = [], array $children = []){
		$this->context       = $context;
		$this->is_toplevel   = $is_toplevel;
		$this->default_child = $default_child;
		$this->children      = $children;
	 }
}
?>