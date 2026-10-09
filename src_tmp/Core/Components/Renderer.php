<?php

namespace SaQle\Core\Components;

use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Http\Request\Request;
use SaQle\Security\Csrf\CsrfToken;

class Renderer {
     
     private ?Node $current_node = null;

     private array $attributes = [
         'title' => '', 
         'meta'  => ''
     ];

     public function __construct(
         private Request $request
     ){}

     public function csrf(){

         $token = CsrfToken::get();

         return "<input type='hidden' id='".CsrfToken::get_key()."' name='".CsrfToken::get_key()."' value='".$token."'>";

     }

     //runtime block rendering api
     public function block(string $name, string $default_template, array $scope) : string {
         $template = $this->current_node?->blocks[$name] ?? $default_template;
         $template = path_join([config('base_path'), $template]);
         extract($scope);
         ob_start();
         include $template;
         return ob_get_clean();
     }

     //runtime component rendering API
     public function component(
         string $name, 
         array  $props = [],  
         array  $blocks = [],
         array  $context = []
     ) : string {

         $component = ComponentRegistry::get($name, true);

         if(!$component){
             return "<!-- component '{$name}' not found -->";
         }  

         $node = new Node($component, $props);

         $node->blocks = $blocks;

         return $this->render($node, $context);
     }

     //recursive tree rendering
     public function render(Node $node, array $context = []) : string {
 
         $node->active = true;

         $previous = $this->current_node;

         $this->current_node = $node;

         $html = $node->render($this->request, $context);

         $this->current_node = $previous;

         $html = $this->inject_dynamic($html, $node);

         return $html;
     }

     //inject dynamic
     private function inject_dynamic(string $html, Node $node) : string {
         // Match <ui:slot />, allowing arbitrary whitespace
         $pattern = '/<ui:slot\s*\/>/i';

         //If no dynamic slot exists, return early
         if(!preg_match($pattern, $html)) {
             return $html;
         }

         //Resolve the dynamic child (by convention: first child)
         $child = $node->children[0] ?? null;

         //If no child exists, remove the dynamic slot entirely
         if(!$child) {
             return preg_replace($pattern, '', $html);
         }

         //Render the child using the parent context
         $rendered = $this->render($child, $node->component->context()->expose());

         //Replace the FIRST occurrence only
         return preg_replace($pattern, $rendered, $html, 1);
     }
} 
