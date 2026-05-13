<?php

namespace SaQle\Core\Components;

use SaQle\Core\Registries\{
     FormBlueprintRegistry, 
     ComponentRegistry
};
use SaQle\Core\Forms\{
     FormRuntimeContext, 
     AutoForm
};
use SaQle\Http\Response\Message;
use SaQle\Http\Request\Request;
use SaQle\Core\Ui\{
     View, 
     AssetManager
};
use RuntimeException;

class ComponentRenderer {
     
     private $attributes = ['css' => [], 'js' => [], 'title' => '', 'meta' => ''];

     public function __construct(private Request $request){

     }

     public function wrap_root(string $html){
         $assets = AssetManager::output();

         $page_component = ComponentRegistry::resolve_component('page', 'GET', 'layout');
         $page = new View($page_component[3]);
         $page->set_context([
             'content' => $html,
             'css'     => $assets['css'],
             'js'      => $assets['js'],
             'title'   => $this->attributes['title'],
             'meta'    => $this->attributes['meta']
         ]);

         return $page->render();
     }

     public function render(ComponentNode $node, array $context = [], array $props = []): string {

         //1. activate node
         $node->active = true;

         //2. get nodes html
         $html = $node->render($this->request, $context);

         //3. resolve dynamic slot
         $html = $this->inject_dynamic($html, $node, $node->context->expose());

         //4. resolve inline components
         $html = $this->inject_inline_components($html, $node, $node->context->expose());

         return $html;
     }

     private function inject_dynamic(string $html, ComponentNode $node, array $context): string {
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

     private function parse_attributes(string $attr_string) : array {
         preg_match_all('/(\w+)=["\']([^"\']+)["\']/', $attr_string, $matches);

         $attrs = [];
         foreach($matches[1] as $i => $key){
             $attrs[$key] = $matches[2][$i];
         }

         return $attrs;
     }

     private function inject_inline_components(string $html, ComponentNode $node, array $context): string {

         $pattern = '/<component:(block|form)\s+([^\/]+)\s*\/>/i';

         return preg_replace_callback($pattern, function($matches) use ($node, $context) {
             
             $type = strtolower($matches[1]);  // 'block' or 'form'

             $attr_string = $matches[2];

             $attrs = $this->parse_attributes($attr_string);

             $name = $attrs['name'] ?? null; //component or form name

             if(!$name){
                 return "<!-- component missing name -->";
             }

             unset($attrs['name']); // rest = props

             if($type === 'block'){
                 //Resolve block from registry
                 $def = ComponentRegistry::get_definition($name);
                 
                 if(!$def){
                    return "<!-- Block '{$name}' not found -->";
                 }

                 $child = new ComponentNode($def);
                 $child->parent = $node;

                 return $this->render($child, $context, $attrs);
             }

             if($type === 'form'){
                 //Resolve AutoForm blueprint
                 try{
                    $blueprint = FormBlueprintRegistry::get($name);
                 }catch(RuntimeException $e){
                     return "<!-- Form '{$name}' not found -->";
                 }

                 //Render form HTML using AutoForm
                 $auto_form = AutoForm::from_blueprint($blueprint);
                 $auto_form->bind(FormRuntimeContext::from_session($blueprint['name']));

                 return $auto_form->render();
             }
             
             return '';

         },  $html);
     }
}
