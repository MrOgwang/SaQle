<?php
declare(strict_types = 0);
namespace SaQle\Templates\Attributes;

#[Attribute(Attribute::TARGET_FUNCTION)]
class Js{
	 private array $files = [];
	 public function __construct(array $files = []){
		$this->files = $files;
	 }
}
?>