<?php

namespace SaQle\Core\Components;

class ComponentDefinition {
     public function __construct(
     	 //the name of the component
         public string $name,

         //the components template path
         public string $template_path,

         //the controller class name
         public ?string $controller = null,

         //the controller method to execute
         public ?string $method = null,
     ) {}
}
