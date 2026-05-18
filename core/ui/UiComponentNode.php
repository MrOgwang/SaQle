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
use Throwable;

class UiComponentNode {
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
                     $this->def->method
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

         $css = $this->def->css();
         $js = $this->def->js();
         AssetManager::add_css($css);
         AssetManager::add_js($js);

         $view = new View($this->def->template_path);
         $view->set_context($this->context->expose());

         return $view->render();
     }
}
