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
     
     private ?UiComponentNode $current_node = null;

     private array $attributes = [
         'css'   => [], 
         'js'    => [], 
         'title' => '', 
         'meta'  => ''
     ];

     public function __construct(
         private Request $request
     ){}

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
         array $props = [],  
         array $blocks = [],
         array $context = []
     ) : string {
 
         $definition = ComponentRegistry::get_definition($name, $props);

         if(!$definition){
             return "<!-- component '{$name}' not found -->";
         }

         $node = new UiComponentNode($definition);

         $node->props = $props;

         $node->blocks = $blocks;

         return $this->render($node, $context);
     }

     //recursive tree rendering
     public function render(UiComponentNode $node, array $context = []) : string {

         $node->active = true;

         //$html = $node->render($this->request, $context);

         $previous = $this->current_node;

         $this->current_node = $node;

         $html = $node->render($this->request, $context);

         $this->current_node = $previous;

         $html = $this->inject_dynamic($html, $node, $node->context->expose());

         return $html;
     }

     //inject dynamic
     private function inject_dynamic(string $html, UiComponentNode $node, array $context) : string {
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
