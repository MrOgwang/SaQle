<?php

namespace SaQle\Core\Ui;

use SaQle\Core\Registries\ComponentRegistry;

class UiComponentDefinition {
     public function __construct(
     	 //the name of the component
         public string $name,

         //base bath
         public string $path = "",

         //the components source template path
         public ?string $template_path = null,

         //the components compiled template path
         public ?string $compiled_template_path = null,

         //the controller class name
         public ?string $controller = null,

         //the component definition
         public ?string $definition = null,

         //the controller method to execute
         public ?string $method = null,

         //whether its a proxy
         public bool $proxy = false,

         //whether component has multiple template variations
         public bool $has_many_templates = false,

         //the component template variations
         public array $template_variations = []
     ){
         /**
          * Resolve decoy components here. A decoy component
          * is used to return a particular component based on a condition
          * */
         /*if($this->controller && is_a($this->controller, $parentClassName, true);){
             'SaQle\\Components\\StaticFile\\StaticFile'
         }*/
     }

     public function to_array(){
         return [
             'name' => $this->name,
             'path' => $this->path,
             'template_path' => $this->template_path,
             'compiled_template_path' => $this->compiled_template_path,
             'controller' => $this->controller,
             'definition' => $this->definition,
             'method' => $this->method,
             'proxy' => $this->proxy,
             'has_many_templates' => $this->has_many_templates,
             'template_variations' => $this->template_variations
         ];
     }

     public static function from_array(array $def){
         return new static(...$def);
     }

     public function get_target(){
         return $this->method ? $this->name."@".$this->method : $this->name;
     }

     private function get_name_from_ref(string $ref){
         $name_array = explode(".", $ref);
         return trim(end($name_array));
     }

     private function get_dependencies() : array {

         if(!$this->definition){
             return ['css' => [], 'js' => []];
         }

         $defclass = $this->definition;

         $def = new $defclass();

         $dependencies = $def->dependencies();

         return [
             'css' => $dependencies['styles'] ?? [],
             'js'  => $dependencies['scripts'] ?? [],
         ];
     }

     private function get_assets(string $type, array &$loaded_components = [], string $template_path = "") : array {
         
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
         $name = $type === "css" ? "Style" : "Script";
         $file = "{$this->path}/{$name}.{$type}";

         if(file_exists($file)){
             $files[] = $file;
         }
         
         $assets = [];
         $listed = [];

         foreach($files as $f){

             $fn = strtolower($f);

             if(!in_array($fn, $listed)){
                 $assets[] = $f;
                 $listed[] = $fn;
             }

         }

         return $assets;
     }

     public function js(array &$loaded_components = [], string $template_path = "") : array {
         return $this->get_assets("js", $loaded_components, $template_path);
     }

     public function css(array &$loaded_components = [], string $template_path = "") : array {
         return $this->get_assets("css", $loaded_components, $template_path);
     }
}
