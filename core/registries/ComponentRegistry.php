<?php

namespace SaQle\Core\Registries;

use SaQle\Core\Assert\Assert;
use SaQle\Core\Support\{HttpMethod, Index};
use SaQle\Core\Components\ComponentDefinition;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

final class ComponentRegistry {
     private static ?array $components = null;

     public static function all(): array{
         if (self::$components === null) {
             self::$components = require path_join([config('base_path'), config('class_mappings_dir'), 'components.php']);
         }

         return self::$components;
     }

     public static function exists(string $name): bool {
         return array_key_exists($name, self::all());
     }

     public static function get(string $name): array {
         $components = self::all();

         if (!isset($components[$name])){
            throw new InvalidArgumentException("Component [$name] does not exist.");
         }

         return $components[$name];
     }

     public static function get_definition(string $name): ComponentDefinition {
         $resolved_component = self::resolve_component($name, 'GET', 'layout');
         return new ComponentDefinition(
             name: $resolved_component[0], 
             path: dirname($resolved_component[3]),
             template_path: $resolved_component[3], 
             controller: $resolved_component[1], 
             method: $resolved_component[2]
         );
     }

     public static function assert_components_exist(array $components){
         foreach($components as $component){
             if(!self::exists($component)){
                 throw new InvalidArgumentException("The component: '{$component}' does not exist!");
             }
         }
     }

     public static function resolve_component(string $reference, string $http_verb, string $type = 'target'){
         $resolved_component = [null, null, null, null]; //[component_name, controller_class_name, controller_method, template_path]

         //target must be a non empty string, otherwise complain loudly
         Assert::stringNotEmpty($reference, 'A component must be a non empty string!');

         $reference_array = explode("@", $reference);
         $component_name = $reference_array[0];

         //assert component exists
         self::assert_components_exist([$component_name]);

         $resolved_component[0] = $component_name;

         /**
          * a component can have at least a template or a controller(purely api components),
          * otherwise it can have both a template and a controller.
          * */
         $component = self::get($component_name);
         $controller = $component['controller'];
         $template_path = $component['template_path'];
         $real_template_path = path_join(
             $component['owner'] === 'project' ? 
             [config('base_path'), $template_path] : 
             [config('framework_path'), $template_path]
         );

         if(!$controller && !$template_path){
             throw new InvalidArgumentException('A component must have a controller, a template or both!');
         }

         //if there is a controller, ensure the class is defined and the action method provided exists
         if($controller){
             if(!class_exists($controller)){
                 throw new InvalidArgumentException("The controller {$controller} does not exist for the component {$component_name}!");
             }

             $resolved_component[1] = $controller;

             $action = $reference_array[1] ?? '';

             //if the action has been provided at this point, ensure the method exists in class
             if($action && !method_exists($controller, $action)){
                 throw new InvalidArgumentException('The method ['.$action.'] does not exist in the class ['.$controller.']');
             }

             //if the method still doesnt exist here, dynamically determine a method to use
             if(!$action){
                 $action = $type === 'target' ? self::resolve_target_action($controller, $http_verb) : self::resolve_layout_action($controller, 'GET');
             }

             $resolved_component[2] = $action;
         }

         //if there is a template_path, ensure the file exists
         if($template_path){
             if(!file_exists($real_template_path)){
                 throw new InvalidArgumentException('The template file: '.$template_path.' does not exist!');
             }

             $extension = pathinfo($template_path, PATHINFO_EXTENSION);

             $template_ext = config('app.component_template_ext');

             if (strtolower($extension) !== strtolower($template_ext)) {
                 throw new InvalidArgumentException("Invalid template file type! Expected an .".$template_ext." file.");
             }

             $resolved_component[3] = $real_template_path;
         }

         return $resolved_component;
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

         //Rule 1: single public method
         if (count($public_methods) === 1) {
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
