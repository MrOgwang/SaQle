<?php

namespace SaQle\Core\Ui;

use SaQle\Core\Registries\ComponentRegistry;

class UiComponentDefinition {
     public function __construct(
     	 //the name of the component
         public string $name,

         //base bath
         public string $path,

         //the components template path
         public ?string $template_path = null,

         //the controller class name
         public ?string $controller = null,

         //the controller method to execute
         public ?string $method = null,

         //whether its a proxy
         public bool $proxy = false
     ){

         /**
          * Resolve decoy components here. A decoy component
          * is used to return a particular component based on a condition
          * */
         /*if($this->controller && is_a($this->controller, $parentClassName, true);){
             'SaQle\\Components\\StaticFile\\StaticFile'
         }*/
     }

     private function get_name_from_ref(string $ref){
         $name_array = explode(".", $ref);
         return trim(end($name_array));
     }

     private function get_dependencies() : array {

         $name = $this->get_name_from_ref($this->name);

         $json_file = "{$this->path}/{$name}.json";

         if(!file_exists($json_file)){
             return ['css' => [], 'js' => []];
         }

         $json = json_decode(file_get_contents($json_file), true);

         return [
             'css' => $json['dependencies']['css'] ?? [],
             'js'  => $json['dependencies']['js'] ?? [],
         ];
     }

     private function get_assets(string $type, array &$loaded_components = []) : array {
         if(isset($loaded_components[$this->name])) {
             return [];
         }

         $loaded_components[$this->name] = true;

         $files = [];

         // 1. Resolve dependencies first
         $deps = $this->get_dependencies()[$type];

         foreach($deps as $dep){
             /**
              * Assets belonging to other components
              * that are to be shared by this component
              * */
             if(str_starts_with($dep, '@')){
                 $component_name = substr($dep, 1);

                 $component = ComponentRegistry::get_definition($component_name);
                 if($component){
                     $files = array_merge($files, $component->$type($loaded_components));
                 }
             }
             /**
              * Assets living outside project.
              * 
              * Expects absolute urls
              * */
             elseif(str_starts_with($dep, '~')){
                 $files[] = $dep;
             }
             /**
              * Global assets living inside this
              * project. 
              * */
             else{
                 $files[] = path_join([config('base_path'), "public/static/{$type}/", "{$dep}.{$type}"]);
             }
         }

         //2. Add this component's own assets
         $name = $this->get_name_from_ref($this->name);
         $file = "{$this->path}/{$name}.{$type}";

         if(file_exists($file)){
             $files[] = $file;
         }

         return array_unique($files);
     }

     public function js(array &$loaded_components = []) : array {
         return $this->get_assets("js", $loaded_components);
     }

     public function css(array &$loaded_components = []) : array {
         return $this->get_assets("css", $loaded_components);
     }
}
