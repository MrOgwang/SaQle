<?php

namespace SaQle\Core\Components;

use SaQle\Core\Registries\ComponentRegistry;

class ComponentDefinition {
     public function __construct(
     	 //the name of the component
         public string $name,

         //base bath
         public string $path,

         //the components template path
         public string $template_path,

         //the controller class name
         public ?string $controller = null,

         //the controller method to execute
         public ?string $method = null,
     ) {}

     private function get_dependencies() : array {
         $json_file = "{$this->path}/{$this->name}.json";

         if(!file_exists($json_file)){
             return ['css' => [], 'js' => []];
         }

         $json = json_decode(file_get_contents($json_file), true);

         return [
             'css' => $json['dependencies']['css'] ?? [],
             'js'  => $json['dependencies']['js'] ?? [],
         ];
     }

     public function js(array &$loaded_components = []) : array {
         if(isset($loaded_components[$this->name])) {
             return [];
         }

         $loaded_components[$this->name] = true;

         $files = [];

         // 1. Resolve dependencies first
         $deps = $this->get_dependencies()['js'];

         foreach($deps as $dep){

             if(str_starts_with($dep, '@')){
                 $component_name = substr($dep, 1);

                 $component = ComponentRegistry::get_definition($component_name);
                 if($component){
                     $files = array_merge($files, $component->js($loaded_components));
                 }
             }else{
                $files[] = path_join([config('base_path'), "public/static/css/", "{$dep}.js"]);
             }
         }

         //2. Add this component's own JS
         $file = "{$this->path}/{$this->name}.js";

         if(file_exists($file)){
             $files[] = $file;
         }

         return array_unique($files);
     }

     public function css(array &$loaded_components = []) : array {
         if(isset($loaded_components[$this->name])){
             return [];
         }

         $loaded_components[$this->name] = true;

         $files = [];

         //1. Resolve dependencies first
         $deps = $this->get_dependencies()['css'];

         foreach($deps as $dep){
             //Component dependency
             if(str_starts_with($dep, '@')){
                 $component_name = substr($dep, 1);
                 $component = ComponentRegistry::get_definition($component_name);
                 if($component){
                     $files = array_merge($files, $component->css($loaded_components));
                 }
             }
             //Global asset
             else{
                 $files[] =  path_join([config('base_path'), "public/static/css/", "{$dep}.css"]);
             }
         }

         //2. Add this component's own CSS
         $file = "{$this->path}/{$this->name}.css";

         if(file_exists($file)){
             $files[] = $file;
         }

         return array_unique($files);
     }
}
