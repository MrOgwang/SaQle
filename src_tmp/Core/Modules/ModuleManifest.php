<?php

namespace SaQle\Core\Modules;

class ModuleManifest {

	 public function __construct(
	 	 public readonly string $name,
         public readonly string $description = "",
         public readonly string $version = '1.0.0',
         public readonly string $author = "SaQle",
         public readonly string $homepage = "",
         public readonly int    $priority = 1
	 ){}
	 
}