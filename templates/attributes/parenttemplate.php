<?php
declare(strict_types = 0);
namespace SaQle\Templates\Attributes;

#[Attribute(Attribute::TARGET_FUNCTION)]
class ParentTemplate{
	 private string $path;
	 private string $name;
	 private string $context_key;
	 public function __construct(string $path, string $name, string $context_key){
	 	$this->path        = $path;
	 	$this->name        = $name;
		$this->context_key = $context_key;
	 }
}
?>