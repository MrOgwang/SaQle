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
use SaQle\Http\Request\Execution\ActionExecutor;
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

     public function render(ComponentNode $node, ?ComponentContext $parent_ctx = null, array $props = []): string {
         
         //1. activate node
         $node->active = true;

         //2. Execute controller
         $http_message = ActionExecutor::execute($this->request, $node->def->controller, $node->def->method);
         $data = $http_message->data ?? [];
         
         if($http_message->should_flash()){
             $request->session->set('flash', (object)[
                 'message' => $http_message->message,
                 'context' => $http_message->data,
                 'code'    => $http_message->code ,
                 'type'    => 'response'
             ], true);
         }

         $node->context = new ComponentContext($data, $parent_ctx);

         //3. Register component assets
         $css = $node->def->css();
         $js = $node->def->js();
         
         AssetManager::add_css($css);
         AssetManager::add_js($js);

         //4. Render compiled view
         $view = new View($node->def->template_path);
         $view->set_context($node->context->expose());
         $html = $view->render();

         //5. Resolve dynamic slot
         $html = $this->inject_dynamic($html, $node);

         //6. Resolve inline components
         $html = $this->inject_inline_components($html, $node);

         return $html;
     }

     private function inject_dynamic(string $html, ComponentNode $node): string {
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
         $rendered = $this->render($child, $node->context);

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

     private function inject_inline_components(string $html, ComponentNode $node): string {

         $pattern = '/<component:(block|form)\s+([^\/]+)\s*\/>/i';

         return preg_replace_callback($pattern, function($matches) use ($node) {
             
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

                 return $this->render($child, $node->context, $attrs);
             }

             if($type === 'form') {
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
