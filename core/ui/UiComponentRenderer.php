<?php

namespace SaQle\Core\Ui;

use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Http\Response\Message;
use SaQle\Http\Request\Request;
use SaQle\Core\Ui\{
     View, 
     AssetManager
};
use RuntimeException;

class UiComponentRenderer {
     
     private array $attributes = [
         'css'   => [], 
         'js'    => [], 
         'title' => '', 
         'meta'  => ''
     ];

     public function __construct(
         private Request $request
     ){}

     //runtime component rendering API
     public function component(string $name, array $props = [], array $context = [], string $slot = '') : string {

         $definition = ComponentRegistry::get_definition($name, $props);

         if(!$definition){
             return "<!-- component '{$name}' not found -->";
         }

         $node = new UiComponentNode($definition);

         $node->props = $props;

         $node->slot = $slot;

         return $this->render($node, $context);
     } 

     //recursive tree rendering
     public function render(UiComponentNode $node, array $context = []) : string {

         $node->active = true;

         $html = $node->render($this->request, $context);

         $html = $this->inject_dynamic($html, $node, $node->context->expose());

         return $html;
     }

     //inject dynamic
     private function inject_dynamic(string $html, UiComponentNode $node, array $context) : string {
         // Match <component:slot />, allowing arbitrary whitespace
         $pattern = '/<component:slot\s*\/>/i';

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
         $rendered = $this->render($child, $context);

         //Replace the FIRST occurrence only
         return preg_replace($pattern, $rendered, $html, 1);
     }

     public function wrap_root(string $html){
         $assets = AssetManager::output();

         $page_component = ComponentRegistry::resolve_component('page', 'GET', 'layout');
         $page = new View($page_component->compiled_template_path);
         $page->set_context([
             'content' => $html,
             'css'     => $assets['css'],
             'js'      => $assets['js'],
             'title'   => $this->attributes['title'],
             'meta'    => $this->attributes['meta']
         ]);

         return $page->render();
     }
} 
