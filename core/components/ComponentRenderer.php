<?php

namespace SaQle\Core\Components;

use SaQle\Core\Registries\{FormBlueprintRegistry, ComponentRegistry};
use SaQle\Core\Forms\{FormRuntimeContext, AutoForm};
use SaQle\Http\Response\HttpMessage;
use SaQle\Http\Request\Execution\ActionExecutor;
use SaQle\Http\Request\Request;
use SaQle\Core\Ui\View;
use SaQle\Commons\StringUtils;
use RuntimeException;

class ComponentRenderer {
     use StringUtils;

     private $attributes = ['css' => [], 'js' => [], 'title' => '', 'meta' => ''];

     public function __construct(private Request $request){

     }

     public function wrap_root(string $html){
         $page_component = ComponentRegistry::resolve_component('page', 'GET', 'layout');

         $page = new View($page_component[3], true);

         return self::set_template_context($page->get_template(), [
             'content' => $html, 
             'title'   => $this->attributes['title'], 
             'css'     => implode("\n", array_unique($this->attributes['css'])), 
             'js'      => implode("\n", array_unique($this->attributes['js'])), 
             'meta'    => $this->attributes['meta']
         ], true);
     }

     public function render(ComponentNode $node, ?ComponentContext $parent_ctx = null): string {
         //1. Execute controller (activate node)
         $node->active = true;

         $data = $this->run_controller($node->def->controller, $node->def->method)->data ?? [];
         $node->context = new ComponentContext($data, $parent_ctx);

         //2. Render template (conditions evaluated here)
         $view = new View($node->def->template_path, true);
         $meta = $view->get_meta();
         $title = $view->get_title();
         $this->attributes['css'] = array_merge($this->attributes['css'], $view->get_css());
         $this->attributes['js'] = array_merge($this->attributes['js'], $view->get_js());
         $this->attributes['title'] = $title ? $title : $this->attributes['title'];
         $this->attributes['meta'] = $meta ? $meta : $this->attributes['meta'];
         $view->set_context($node->context->expose());
         $html = $view->view();

         //3. Resolve dynamic slot
         $html = $this->inject_dynamic($html, $node);

         //4. Resolve inline components
         $html = $this->inject_inline_components($html, $node);

         return $html;
     }

     private function inject_dynamic(string $html, ComponentNode $node): string {
         // Match <component:dynamic />, allowing arbitrary whitespace
         $pattern = '/<component:slot\s*\/>/i';

         // If no dynamic slot exists, return early
         if (!preg_match($pattern, $html)) {
             return $html;
         }

         // Resolve the dynamic child (by convention: first child)
         $child = $node->children[0] ?? null;

         // If no child exists, remove the dynamic slot entirely
         if (!$child) {
             return preg_replace($pattern, '', $html);
         }

         // Render the child using the parent context
         $rendered = $this->render($child, $node->context);

         // Replace the FIRST occurrence only
         return preg_replace($pattern, $rendered, $html, 1);
     }

     private function inject_inline_components(string $html, ComponentNode $node): string {

         // Regex matches either <component:block name='...'> or <component:form name='...'/>
         $pattern = '/<component:(block|form)\s+name=[\'"]([\w\-]+)[\'"]\s*\/>/i';

         return preg_replace_callback($pattern, function($matches) use ($node) {
             
             $type = strtolower($matches[1]);  // 'block' or 'form'
             $name = $matches[2];               // component or form name

             if($type === 'block'){
                 //Resolve block from registry
                 $def = ComponentRegistry::get_definition($name);
                 if(!$def){
                    return "<!-- Block '{$name}' not found -->";
                 }

                 $child = new ComponentNode($def);
                 $child->parent = $node;

                 return $this->render($child, $node->context);
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

     private function run_controller(?string $class = null, ?string $method = null): HttpMessage {
         if(!$class || !$method)
             return ok();

         return (new ActionExecutor())->execute($this->request, $class, $method);
     }
}
