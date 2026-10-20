<?php

namespace SaQle\Core\Components;

use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Http\Request\Request;
use SaQle\Http\Message\Message;
use LogicException;

class Tree {

     private function get_component(Request $request, string $name, bool $is_success = true, bool $is_target = false){

         if($is_target && $request->target !== null && $is_success){
             return $request->target;
         }

         return ComponentRegistry::get_or_fail($name, true);

     }

     /**
     * Builds a linear component tree where each component
     * is the parent of the one to its right.
     *
     * @param string[] $layouts
     *
     * @return Node  Root (top-most) component node
     */
     public function build(Request $request, array $layouts, Message $result) : Node {
         //1. Normalize input into a single ordered list
         $layouts = array_values($layouts);

         if(empty($layouts)){
             throw new LogicException('Cannot build component tree from empty input.');
         }

         //2. Create all nodes first
         $nodes = [];
         
         foreach($layouts as $i => $l){

             $component = $this->get_component($request, $l, $result->is_success(), $i === count($layouts) - 1);

             $node = new Node($component);

             $nodes[] = $node;
         }

         //3. Link parent <-> child relationships
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
