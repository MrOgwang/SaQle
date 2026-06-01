<?php

namespace SaQle\Core\Ui;

use SaQle\Core\Ui\{
     View, 
     AssetManager
};
use SaQle\Http\Request\Execution\ActionExecutor;
use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\Message;
use SaQle\Core\Ui\Template;
use Throwable;

class UiComponentNode {

     public string $slot = '';

     //component attributes passed via <component/> tag in html
     public array $props = [];

     //whether to execute component controller or not
     private bool $execute = true;

     //component definition
     public UiComponentDefinition $def;

     //the parent component
     public ?UiComponentNode $parent = null;

     //array of component nodes
     public array $children = [];

     //whether component is activated or not
     public bool $active = false;

     //component context data
     public ?UiComponentContext $context = null;

     public function __construct(UiComponentDefinition $def){
         $this->def = $def;
     }

     public function execute_controller(bool $execute = true){
         $this->execute = $execute;
     }

     public function with_context(array $context){
         $this->context = new UiComponentContext($context, new UiComponentContext($context));
     } 

     //let nodes self render
     public function render(Request $request, array $parent_context) : string {

         if($this->execute && $this->def->controller && $this->def->method){
             try{
                $this_context = ActionExecutor::execute(
                     $request, 
                     $this->def->controller, 
                     $this->def->method,
                     $this->props
                 )->data ?? [];
                 $this->context = new UiComponentContext($this_context);
             }catch(Throwable $e){
                 $this->def = ComponentRegistry::get_definition(config('error.component'));

                 $http_message = $e instanceof FrameworkException ? 
                 $e->get_http_message() : 
                 new Message(Message::INTERNAL_SERVER_ERROR, $e->getTrace(), $e->getMessage());

                 $this->context = new UiComponentContext([
                     'code'    => $http_message->code,
                     'message' => $http_message->message,
                     'data'    => $http_message->data
                 ]);
             }
         }

         if(is_null($this->context)){
             $this->context = new UiComponentContext([]);
         }

         $this->context->parent_context(new UiComponentContext($parent_context));

         $compiled_template_path = $this->def->compiled_template_path;
         $template_path = $this->def->template_path;
         
         if($this->def->has_many_templates){
             $resolver = Template::get_resolver($this->def->name);
             if($resolver){
                 $template_key = $resolver($request, $this->props);
                 $compiled_template_path = $this->def->template_variations[$template_key]['compiled_template_path'] ?? $compiled_template_path;
                 $template_path = $this->def->template_variations[$template_key]['template_path'] ?? $template_path;
             }
         }

         $css_loaded_components = [];
         $js_loaded_components = [];
         $css = $this->def->css($css_loaded_components, $template_path);
         $js = $this->def->js($js_loaded_components , $template_path);
         AssetManager::add_css($css);
         AssetManager::add_js($js);

         $view = new View($compiled_template_path);

         $renderer = null;
         if(isset($parent_context['__renderer'])){
             $renderer = $parent_context['__renderer'];
         } 
         $view->set_context(array_merge(
             $this->context->expose(),
             [
                 '__props' => $this->props,
                 '__slot'  => $this->slot,
                 '__renderer' => $renderer,
                 '__context' => $parent_context
             ],
             $this->props
         ));
         
         return $view->render();
     }
}
