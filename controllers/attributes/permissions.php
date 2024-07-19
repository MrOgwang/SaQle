<?php
namespace SaQle\Controllers\Attributes;

#[\Attribute(Attribute::TARGET_CLASS)]
class Permissions{
	 public function __construct(private array $permissions){

	 }
}
?>