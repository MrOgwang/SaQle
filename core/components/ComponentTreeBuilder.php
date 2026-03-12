<?php

namespace SaQle\Core\Components;

use SaQle\Core\Registries\ComponentRegistry;
use LogicException;

class ComponentTreeBuilder {
     /**
     * Builds a linear component tree where each component
     * is the parent of the one to its right.
     *
     * @param string   $leaf_component
     * @param string[] $wrappers
     *
     * @return ComponentNode  Root (top-most) component node
     */
     public function build(string $leaf_component, array $wrappers) : ComponentNode {
         // 1. Normalize input into a single ordered list
         $component_names = array_values(array_merge($wrappers, [$leaf_component]));

         if(empty($component_names)){
             throw new LogicException('Cannot build component tree from empty input.');
         }

         //2. Create all nodes first
         $nodes = [];

         foreach ($component_names as $component_name){
             $definition = ComponentRegistry::get_definition($component_name);

             if(!$definition) {
                 throw new LogicException("Component definition not found for '{$component_name}'.");
             }

             $nodes[] = new ComponentNode($definition);
         }

         // 3. Link parent <-> child relationships
         $count = count($nodes);

         for ($i = 0; $i < $count - 1; $i++) {
             $parent = $nodes[$i];
             $child  = $nodes[$i + 1];

             $parent->children[] = $child;
             $child->parent = $parent;
         }

         //4. First element is always the root
         return $nodes[0];
     }
}
