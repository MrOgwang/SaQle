<?php

namespace SaQle\Core\Components;

use SaQle\Core\Registries\ComponentRegistry;

class ComponentTreeBuilder {

     /**
     * @param string $leaf_component   e.g. 'about'
     * @param string[] $wrappers      e.g. ['landing']
     */
     public function build(string $leaf_component, array $wrappers): ComponentNode {
         
         $leaf = new ComponentNode(ComponentRegistry::get_definition($leaf_component));

         $current = $leaf;

         foreach($wrappers as $wrapper_name) {
             $wrapper = new ComponentNode(ComponentRegistry::get_definition($wrapper_name));

             $wrapper->children[] = $current;

             $current->parent = $wrapper;

             $current = $wrapper;
         }

         return $current; // top-most node
     }
}
