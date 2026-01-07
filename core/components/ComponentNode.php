<?php

namespace SaQle\Core\Components;

class ComponentNode {
     //component definition
     public ComponentDefinition $def;

     //the parent component
     public ?ComponentNode $parent = null;

     //array of component nodes
     public array $children = [];

     //whether component is activated or not
     public bool $active = false;

     //component context data
     public ?ComponentContext $context = null;

     public function __construct(ComponentDefinition $def) {
         $this->def = $def;
     }
}
