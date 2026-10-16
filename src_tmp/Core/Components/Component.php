<?php

namespace SaQle\Core\Components;

use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Core\Components\Abstracts\AbstractDefinition;
use SaQle\Core\Ui\PageManager;
use ReflectionClass;
use ReflectionProperty;
use SaQle\Http\Request\Request;

class Component {

     /**
      * The active component: the component being rendered
      * or executed
      * */
     private static ?Component $current = null;

     //the name of the component
     protected string $name;

     //override dir name
     protected ?string $override_dir = null;

     //base bath
     protected ?string $path = null;

     //base bath
     protected ?string $base_path = null;

     //source base bath
     protected ?string $source_path = null;

      //the component owner: app or saqle
     protected ?string $owner = null;

     //the component source owner: app or saqle
     protected ?string $source_owner = null;

     //the controller class name
     protected ?string $controller = null;

     //the controller path
     protected ?string $controller_path = null;

     //whether its a proxy
     protected bool $proxy = false;

     //the component definition
     protected ?string $definition = null;

     //the component definition path
     protected ?string $definition_path = null;

     //the components source template path
     protected ?string $template_path = null;

     //the components compiled template path
     protected ?string $compiled_template_path = null;

     //the component template variations
     protected array $template_variations = [];

     //the component style path
     protected ?string $style_path = null;

     //the component script path
     protected ?string $script_path = null;

     //the controller method to execute
     protected ?string $method = null;

     //the component context
     private Context $context;

     private string $selected_template_path = "";

     private ?string $selected_template_name = null;

     private bool $initialized = false;

     private ?AbstractDefinition $def = null;

     public function __construct(string $name){

         $this->name = $name;

         $this->override_dir = str_replace(" ", "_", ucwords(str_replace(".", " ", $this->name)));

         $this->owner = str_starts_with($this->name, "app.") || $this->has_override() ? "project" : "framework";

         $this->source_owner = $this->owner;

         $this->context = new Context();
     }

     public static function current(?Component $component = null): ?static {
         
         if($component !== null) {
             self::$current = $component;
         }

         return self::$current;
     }

     public function initialize(Request $request, array $props = []){

         $this->selected_template_path = path_join([config('base_path'), $this->compiled_template_path]);
         
         if($this->definition){

             $def_class = $this->definition;
             $def = new $def_class($props);

             if($this->has_many_templates()){

                 $t = $def->template($request);

                 if($t){

                     $this->selected_template_name = strtolower($t);

                     $this->selected_template_path = path_join([
                         config('base_path'),
                         $this->template_variations[$this->selected_template_name]['compiled_template_path']
                     ]); 
                 }
             }

             $this->def = $def;

         }

         $this->initialized = true;

     }

     public function finalize(Request $request){
         if($this->def){
             $this->def->finalize();
         }
     }

     public function get_selected_template_name() : ?string {
         return $this->selected_template_name;
     }

     public function get_selected_template_path() : string {
         return $this->selected_template_path;
     }

     public function is_initialized() : bool {
         return $this->initialized;
     }

     public function name(string $name) : static {

         $this->name = $name;

         return $this;
     }

     public function get_name() : string {
         return $this->name;
     }

     public function override_dir(?string $dir = null) : static {

         $this->override_dir = $dir;

         return $this;
     }

     public function get_override_dir() : ?string {
         return $this->override_dir;
     }

     public function get_override_path() : string {
         return path_join([config('base_path'), "src", "Components", $this->override_dir ?? ""]);
     }

     public function has_override() : bool {

         return is_dir($this->get_override_path());
     }

     public function path(?string $path = null) : static {

         $this->path = $path;

         return $this;
     }

     private function wrap_path(?string $path = null) : string {
         return $this->owner === 'project' ? 
         path_join([config('base_path'), $path ?? ""]) : 
         path_join([config('framework_path'), $path ?? ""]);
     }

     private function wrap_source_path(?string $path = null) : string {
         return $this->source_owner === 'project' ? 
         path_join([config('base_path'), $path ?? ""]) : 
         path_join([config('framework_path'), $path ?? ""]);
     }

     public function get_path() : ?string {
         return $this->wrap_path($this->path);
     }

     public function base_path(?string $path = null) : static {

         $this->base_path = $path;

         return $this;
     }

     public function get_base_path() : ?string {
         return $this->wrap_path($this->base_path);
     }

     public function source_path(?string $path = null) : static {

         $this->source_path = $path;

         return $this;
     }

     public function get_source_path() : ?string {
         return $this->wrap_source_path($this->source_path);
     }

     public function owner(?string $owner = null) : static {
         $this->owner = $owner;

         return $this;
     }

     public function get_owner() : ?string {
         return $this->owner;
     }

     public function source_owner(?string $owner = null) : static {
         $this->source_owner = $owner;

         return $this;
     }

     public function get_source_owner() : ?string {
         return $this->source_owner;
     }

     public function controller(?string $c = null) : static {
         $this->controller = $c;

         return $this;
     }

     public function get_controller() : ?string {
         return $this->controller;
     }

     public function controller_path(?string $p = null) : static {
         $this->controller_path = $p;

         return $this;
     }

     public function get_controller_path() : ?string {
         return $this->wrap_path($this->controller_path);
     }

     public function proxy(bool $proxy) : static {
         $this->proxy = $proxy;

         return $this;
     }

     public function get_proxy() : bool {
         return $this->proxy;
     }

     public function definition(?string $d = null) : static {
         $this->definition = $d;

         return $this;
     }

     public function get_definition() : ?string {
         return $this->definition;
     }

     public function definition_path(?string $p = null) : static {
         $this->definition_path = $p;

         return $this;
     }

     public function get_definition_path() : ?string {
         return $this->wrap_path($this->definition_path);
     }

     public function template_path(?string $p = null) : static {
         $this->template_path = $p;

         return $this;
     }

     public function get_template_path() : ?string {
         return $this->wrap_path($this->template_path);
     }

     public function compiled_template_path(?string $p = null) : static {
         $this->compiled_template_path = $p;

         return $this;
     }

     public function get_compiled_template_path() : ?string {
         return path_join([config('base_path'), $this->compiled_template_path]);
     }

     public function template_variations(array $variations) : static {
         $this->template_variations = $variations;

         return $this;
     }

     public function add_template_variation($name, $template_path, $compiled_template_path) : static {

         $this->template_variations[$name] = [
             'template_path' => $template_path,
             'compiled_template_path' => $compiled_template_path
         ];

         return $this;
     }

     public function get_template_variations() : array {
         return $this->template_variations;
     }

     public function style_path(?string $p = null) : static {
         $this->style_path = $p;

         return $this;
     }

     public function get_style_path() : ?string {
         return $this->wrap_path($this->style_path);
     }

     public function script_path(?string $p = null) : static {
         $this->script_path = $p;

         return $this;
     }

     public function get_script_path() : ?string {
         return $this->wrap_path($this->script_path);
     }

     public function has_many_templates() : bool {
         return count($this->template_variations) > 1;
     }

     public function to_array() : array {
         $reflection = new ReflectionClass($this);
         $properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);

         $data = [];

         foreach($properties as $property){
             if($property->isInitialized($this)){
                 $data[$property->getName()] = $property->getValue($this);
             }
         }

         return $data;
     }

     public static function from_array(array $data): static {

         $instance = new static(name: "");

         foreach($data as $property => $value){

             $setter = $property;

             if(method_exists($instance, $setter)){
                 $instance->{$setter}($value);
             }
         }

         return $instance;
     }

     public function method(?string $m = null) : static {
         $this->method = $m;

         return $this;
     }

     public function get_method() : ?string {
         return $this->method;
     }

     public function context() : Context {
         return $this->context;
     }

     private function get_dependencies(?AbstractDefinition $def = null) : array {
        
         if(!$def){
             return ['css' => [], 'js' => []];
         }

         $dependencies = $def->dependencies();

         return [
             'css' => $dependencies['styles'] ?? [],
             'js'  => $dependencies['scripts'] ?? [],
         ];
     }

     private function get_assets(string $type, ?string $template_name = null) : array {

         $page_manager = PageManager::init();

         [$component_loaded, $template_loaded] = $page_manager->is_loaded($type, $this->name, $template_name);

         if($component_loaded && $template_loaded){
             return [];
         }

         $page_manager->set_loaded($type, $this->name, $template_name);

         $def = null;

         $name = $type === "css" ? "Style" : "Script";

         if($this->definition){
             $defclass = $this->definition;
             $def = new $defclass();
         }

         $files = [];

         if(!$component_loaded){

             // 1. Resolve dependencies first
             $deps = $this->get_dependencies($def)[$type];

             foreach($deps as $dep){
                 /**
                  * Assets belonging to other components
                  * that are to be shared by this component
                  * */
                 if(str_starts_with($dep, '@')){
                     $component_name = substr($dep, 1);

                     $component = ComponentRegistry::get($component_name, true);
                     if($component){
                         $files = array_merge($files, $component->$type());
                     }
                 }
                 /**
                  * Assets living outside project.
                  * 
                  * Expects absolute urls
                  * */
                 elseif(str_starts_with($dep, '~')){
                     $files[] = (Object)[
                         "file" => $dep,
                         'name' => null
                     ];
                 }
                 /**
                  * Global assets living inside this
                  * project. 
                  * */
                 else{
                     $files[] = (Object)[
                         'file' => path_join([config('base_path'), "public/static/{$type}/", "{$dep}.{$type}"]),
                         'name' => null
                     ];
                 }
             }
     
             //2. Add this component theme assets if available
             $theme = $def ? $def->theme() : "Default";
             $theme_file = $this->get_path()."/Themes/{$theme}/{$name}.{$type}";

             if(file_exists($theme_file)){
                 $files[] = (Object)[
                     'file' => $theme_file,
                     'name' => strtolower($theme)."_{$this->name}.{$type}"
                 ];
             }

             //3. Add this component's own assets
             $file = $this->get_path()."/{$name}.{$type}";

             if(file_exists($file)){
                 $files[] = (Object)[
                     'file' => $file,
                     'name' => "{$this->name}.{$type}"
                 ];
             }

         }

         if(!$template_loaded && $template_name){
             
             //4. Add the component's template assets.
             
             $template_file = $this->get_path()."/Templates/{$template_name}/{$name}.{$type}";

             if(file_exists($template_file)){
                 $files[] = (Object)[
                     'file' => $template_file,
                     'name' => strtolower($template_name)."_{$this->name}.{$type}"
                 ];
             }

         }

         return $files;
     }

     public function js(?string $template_name = null) : array {
         return $this->get_assets("js", $template_name);
     }

     public function css(?string $template_name = null) : array {
         return $this->get_assets("css", $template_name);
     }

     /**
      * Basically checks whether a component has a valid controller
      * and a valid method.
      * */
     public function is_executable() : bool {
         if($this->controller && class_exists($this->controller) && 
            $this->method && method_exists($this->controller, $this->method)){
             return true;
         }

         return false;
     }

     /**
      * Get to render template path. This is the compiled template path
      * or compiled template variation.
      * */
     public function get_rendered_template(Request $request){

         $template_path = $this->compiled_template_path;
 
         $template = null;
 
         if($this->has_many_templates() && $this->definition){

             $template_name = $definition->template($request);

             if($template_name){

                 $template_name = strtolower($template_name);

                 $compiled_template_path = $this->component->get_template_variations()[$template_name]['compiled_template_path'] ?? $compiled_template_path;

                 $compiled_template_path = path_join([config('base_path'), $compiled_template_path]);
             }
         }

         return [$template, $template_path];

     }
}
