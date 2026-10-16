<?php

namespace SaQle\Core\Components;

use SaQle\Core\Ui\{
     View, 
     PageManager
};
use SaQle\Http\Request\Execution\ActionExecutor;
use SaQle\Http\Request\Request;
use Throwable;

class Node {

     //overridable regions in the component
     public array $blocks = [];

     //the component
     public Component $component;

     //the parent component
     public ?Node $parent = null;

     //array of component nodes
     public array $children = [];

     //html props
     private array $props = [];

     //whether component is activated or not
     public bool $active = false {
         set(bool $value){
             $this->active = $value;

             if($this->active){

                 Component::current($this->component);

                 if(!$this->component->is_initialized()){
                     $this->component->initialize(request(), $this->props);
                 }
             }
         }

         get => $this->active;
     }

     public function __construct(Component $component, array $props = []){
         
         $this->component = $component;

         $this->props = $props;
     }

     private function set_parent_context(array $parent_props = [], array $parent_data = []){
         $parent_context = new Context();
         $parent_context->props()->replace($parent_props);
         $parent_context->data()->replace($parent_data);

         $this->component->context()->parent($parent_context);
     }

     //let nodes self render
     public function render(Request $request, array $parent_props = [], array $parent_data = []) : string {
 
         //set parent context
         $this->set_parent_context($parent_props, $parent_data);

         $css = $this->component->css($this->component->get_selected_template_name());
         $js = $this->component->js($this->component->get_selected_template_name());

         $page_manager = PageManager::init();

         $page_manager->add_css($css);
         $page_manager->add_js($js);
         
         $view = new View($this->component->get_selected_template_path()); 

         $view->set_context($this->component->context()->expose());
         
         $html = $view->render();

         $metadata = $this->extract_page_metadata($html);

         $page_manager->add_meta(implode("\n", $metadata['meta']));

         $page_manager->set_title($metadata['title'] ?? "");

         return $metadata['html'];
     }

     private function extract_page_metadata(string $html): array {
         $metadata = [
             'meta' => [],
             'title' => null,
         ];

         $patterns = [
             'meta' => '/@meta\s*(.*?)\s*@endmeta/is',
             'title' => '/@title\s*(.*?)\s*@endtitle/is',
         ];

         foreach ($patterns as $type => $pattern){
             $html = preg_replace_callback(
                 $pattern,
                 function (array $matches) use (&$metadata, $type): string {
                     $content = trim($matches[1]);

                     if($type === 'meta'){
                         $metadata['meta'][] = $content;
                     }else{
                         $metadata['title'] = $content;
                     }

                     return '';
                 },
                 $html
             );
         }

         $metadata['html'] = $html;

         return $metadata;
     }
}
