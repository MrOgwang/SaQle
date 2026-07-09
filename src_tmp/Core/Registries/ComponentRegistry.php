<?php

namespace SaQle\Core\Registries;

use SaQle\Core\Assert\Assert;
use SaQle\Core\Support\{HttpMethod, Index};
use SaQle\Core\Ui\UiComponentDefinition;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

final class ComponentRegistry {

     private static bool $_reload = false;

     private static ?array $components = null;

     public static function reload(bool $reload = true){
         self::$_reload = $reload;
     }

     public static function all(bool $reload = false): array {

         if(self::$components === null || $reload === true || self::$_reload === true){
             self::$components = require path_join([config('base_path'), config('class_mappings_dir'), 'components.php']);
         }

         return self::$components;
     }

     public static function exists(string $name): bool {
         return array_key_exists($name, self::all());
     }

     public static function real_template_path(string $template_path, string $owner){
         if(!trim($template_path)){
             return "";
         }
         
         $real_path = path_join(
             $owner === 'project' ? 
             [config('base_path'), $template_path] : 
             [config('framework_path'), $template_path]
         );

         return $real_path;
     }

     public static function get(string $name): array {
         $components = self::all();

         if(!isset($components[$name])){
             throw new InvalidArgumentException("Component [$name] does not exist.");
         }

         return $components[$name];
     }

     public static function get_definition(string $name, array $props = []) : UiComponentDefinition {
         return self::resolve_component($name, 'GET', 'layout', $props);
     }

     public static function assert_components_exist(array $components){
         foreach($components as $component){
             if(!self::exists($component)){
                 throw new InvalidArgumentException("The component: '{$component}' does not exist!");
             }
         }
     }

     private static function get_autoform_action(array $props){
         $mode = $props['mode'] ?? null;

         if(!$mode || $mode === "create"){
             return "show_create_form";
         }

         return "show_edit_form";
     }

     public static function resolve_component(
         string $reference, string $http_verb, string $type = 'target', array $props = []
     ) : UiComponentDefinition
     {
         //reference must be a non empty string, otherwise complain loudly
         Assert::stringNotEmpty($reference, 'A component must be a non empty string!');

         $reference_array = explode("@", $reference);
         $component_name = $reference_array[0];

         //assert component exists
         self::assert_components_exist([$component_name]);

         $component_def = new UiComponentDefinition(name: $component_name);

         /**
          * a component can have at least a template or a controller(purely api components),
          * otherwise it can have both a template and a controller.
          * */
         $component = self::get($component_name);
         $component_def->proxy = $component['proxy'];
         $component_def->has_many_templates = $component['has_many_templates'];
         $component_def->template_variations = $component['template_variations'];

         $controller = $component['controller'];
         $template_path = $component['template_path'];
         $compiled_template_path = path_join([config('base_path'), $component['compiled_template_path']]);

         $real_template_path = self::real_template_path(
             $component['template_path'],
             $component['owner']
         );

         if(!$controller && !$template_path){
             throw new InvalidArgumentException('A component must have a controller, a template or both!');
         }

         //if there is a controller, ensure the class is defined and the action method provided exists
         if($controller){
             if(!class_exists($controller)){
                 throw new InvalidArgumentException("The controller {$controller} does not exist for the component {$component_name}!");
             }

             $component_def->controller = $controller;

             $action = $reference_array[1] ?? '';
             if(!$action && $component_name === "saqle.autoresource"){
                 $action = self::get_autoform_action($props);
             }

             //if the action has been provided at this point, ensure the method exists in class
             if($action && !method_exists($controller, $action)){
                 throw new InvalidArgumentException('The method ['.$action.'] does not exist in the class ['.$controller.']');
             }

             //if the method still doesnt exist here, dynamically determine a method to use
             if(!$action){
                 $action = $type === 'target' ? self::resolve_target_action($controller, $http_verb) : self::resolve_layout_action($controller, 'GET');
             }

             $component_def->method = $action;
         }

         //if there is a template_path, ensure the file exists
         if($real_template_path && $compiled_template_path){
             if(!file_exists($real_template_path)){
                 throw new InvalidArgumentException('The real template file: '.$real_template_path.' does not exist!');
             }

             if(!file_exists($compiled_template_path)){
                 throw new InvalidArgumentException('The compiled template file: '.$compiled_template_path.' does not exist!');
             }

             $extension = pathinfo($real_template_path, PATHINFO_EXTENSION);

             $template_ext = config('app.component_template_ext');

             if(strtolower($extension) !== strtolower($template_ext)) {
                 throw new InvalidArgumentException("Invalid template file type! Expected an .".$template_ext." file.");
             } 

             $component_def->path = dirname($real_template_path);
             $component_def->template_path = $real_template_path;
             $component_def->compiled_template_path = $compiled_template_path;
         }

         return $component_def;
     }

     private static function http_method_rule(): callable {
         return function(array $methods, string $verb){
             return array_values(array_filter($methods, function($method) use ($verb) {
                 foreach ($method->getAttributes(HttpMethod::class) as $attr) {
                     $instance = $attr->newInstance();
                     if (in_array(strtoupper($verb), array_map('strtoupper', $instance->methods))) {
                         return true;
                     }
                 }
                 return false;
             }));
         };
     }

     private static function index_rule(): callable {
         return fn(array $methods) => array_values(array_filter($methods, fn($m) => !empty($m->getAttributes(Index::class))));
     }

     private static function verb_name_rule(): callable {
         return fn(array $methods, string $verb) => array_values(array_filter($methods, fn($m) => strtolower($m->getName()) === strtolower($verb)));
     }

     private static function resolve_action(string $class_name, string $http_verb, array $rules, string $context): string {
         if (!class_exists($class_name)) {
             throw new RuntimeException("Class '$class_name' does not exist!");
         }

         $reflection = new ReflectionClass($class_name);
         $public_methods = array_filter($reflection->getMethods(ReflectionMethod::IS_PUBLIC), fn($m) => !$m->isConstructor() && !$m->isStatic());
         $public_methods = array_values($public_methods);

         //Rule 1: single public method
         if (count($public_methods) === 1){
             return $public_methods[0]->getName();
         }

         foreach ($rules as $rule){
             $matches = $rule($public_methods, $http_verb);

             if (count($matches) === 1) {
                 return $matches[0]->getName();
             }

             if (count($matches) > 1) {
                 $names = implode(', ', array_map(fn($m) => $m->getName(), $matches));
                 throw new RuntimeException("Multiple methods found while resolving {$context} action for {$class_name}. Matching methods: $names!");
             }
         }

         $method_names = implode(', ', array_map(fn($m) => $m->getName(), $public_methods));
         throw new RuntimeException("No suitable method found while resolving {$context} action for {$class_name}. Public methods: $method_names!");
     }

     private static function resolve_target_action(string $class_name, string $http_verb){
         return self::resolve_action(
             $class_name,
             $http_verb,
             [
                 self::http_method_rule(),
                 self::verb_name_rule(),
                 self::index_rule(),
             ],
             'target'
         );
     }

     private static function resolve_layout_action(string $class_name, string $http_verb){
         return self::resolve_action(
             $class_name,
             $http_verb,
             [
                 self::index_rule(),
                 self::verb_name_rule(),
             ],
             'layout'
         );
     }
}
