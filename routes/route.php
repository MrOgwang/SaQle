<?php
/**
 * A route object
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use SaQle\Core\Assert\Assert;
use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Templates\Template;
use SaQle\Http\Methods\Attributes\{HttpMethod, Index};
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

final class Route {
     //the route url
     public string $url {
         set(string $value){
             //the url must be a non empty string, otherwise complain loudly
             Assert::stringNotEmpty($value, 'A route url must be a non empty string!');

             //normalize the url
             $this->url = $this->normalize_url($value);
         }

         get => $this->url;
     }

     //the http method to handle
     public string $method {
         set(string $value){
             $value = strtoupper($value);

             //the method must be a valid http method, otherwise complain loudly
             Assert::oneOf($value, ['POST', 'PUT', 'GET', 'PATCH', 'DELETE']);

             $this->method = $value;
         }

         get => $this->method;
     }

     public private(set) array $compiled_target {
         set(array $value){
             $this->compiled_target = $value;
         }

         get => $this->compiled_target;
     }

     /**
      * componentname@method  - the component name and the method to execute
      * componentname         - just the component name, the method to excute will be determined automatically if a component has a controller
      * 
      * */
     public string $target {
         set(string $value){
             [$compiled_target, $component_name] = $this->get_compiled_target($value, 'target');

             $this->compiled_target = $compiled_target;

             $this->target = !is_null($compiled_target[1]) ? $component_name."@".$compiled_target[1] : $component_name;
         }

         get => $this->target;
     }
     
     /**
      * For web requests, the route will declare which components
      * the final UI layout will be composed with
      *
      * */
     public ?array $layout = null {
         set(?array $value){
             $this->layout = $value;
         }

         get => $this->layout;
     }

     /**
      * These are roles, permissions and attributes as the developer will have defined
      * in the AuthorizationProvider class that will determine whether the user 
      * is authorized to access this route or not
      * */
     public ?array $guards = null{
         set(?array $value){
             //guards must be an array of non empty strings, otherwise complain loudly
             Assert::allStringNotEmpty($value, 'Please provide an array of non empty string names for roles, permissions and attributes');

             $this->guards = $value;
         }

         get => $this->guards;
     }

     /**
      * The guard level determines how the guards are evaluated: options include
      * 
      * all - the user must pass all the listed guards
      * any - the user need only pass one of the listed guards
      * */
     private string $guard_level = 'all';

     /**
      * This is a list of the response types to be returned from this route. options include
      * 
      * html - respond with html, this is for web requests
      * json - respond with json data for api requests
      * sse  - respond with an event stream for server sent event requests
      * */
     public ?array $restype = null{
         set(?array $value){
             //check valid response types, otherwise complain loudly
             foreach($value as $t){
                 Assert::oneOf($t, ['html', 'json', 'sse']);
             }

             $this->restype = $value;
         }

         get => $this->restype;
     }


     //create a new route object
	 public function __construct(string $method, string $url, string $target){
         $this->method = $method;
         $this->url    = $url;
         $this->target = $target;

         //routes respond with html by default
         $this->restype = ['html'];
	 }

     /**
      * Normalize a route url by:
      * 1. Ensuring a leading slash
      * 2. Removing trailing slash
      * */
     private function normalize_url(string $url): string {
         //Ensure leading slash
         if($url === '') return '/';
         if($url[0] !== '/') $url = '/'.$url;
    
         //Remove trailing slash (except for root '/')
         if(strlen($url) > 1){
             $url = rtrim($url, '/');
         }

         return $url;
     }

     private function find_appropriate_action(string $class_name){
         if (!class_exists($class_name)) {
             throw new RuntimeException("Class '$class_name' does not exist for the route: ".$this->url);
         }

         $reflection = new ReflectionClass($class_name);
         $public_methods = array_filter($reflection->getMethods(ReflectionMethod::IS_PUBLIC), fn($m) => !$m->isConstructor() && !$m->isStatic());

         //1. Only one public method
         if(count($public_methods) === 1){
             return $public_methods[0]->getName();
         }

         //2. Methods with HttpMethod attribute matching the HTTP verb
         $http_verb = $this->method;
         $http_methods = array_filter($public_methods, function($method) use ($http_verb){
             $attributes = $method->getAttributes(HttpMethod::class);
             foreach ($attributes as $attr) {
                 $instance = $attr->newInstance();
                 if (in_array(strtoupper($http_verb), array_map('strtoupper', $instance->methods))) {
                     return true;
                 }
             }
             return false;
         });

         if(count($http_methods) === 1) {
             return array_values($http_methods)[0]->getName();
         }

         if (count($http_methods) > 1) {
             $names = implode(', ', array_map(fn($m) => $m->getName(), $http_methods));
             throw new RuntimeException("Multiple methods found for HTTP verb ".$http_verb." for the route: ".$this->url.". Please specify. Matching methods: $names");
         }

         // 3. Match method name to HTTP verb
         $verb_name = strtolower($this->method);

         foreach ($public_methods as $method) {
             if (strtolower($method->getName()) === $verb_name) {
                 return $method->getName();
             }
         }

         //3. Methods with Index attribute
         $index_methods = array_filter($public_methods, fn($m) => !empty($m->getAttributes(Index::class)));

         if(count($index_methods) === 1) {
              return array_values($index_methods)[0]->getName();
         }

         if(count($index_methods) > 1) {
             $names = implode(', ', array_map(fn($m) => $m->getName(), $index_methods));
             throw new RuntimeException("Multiple methods found with Index attribute for the route: ".$this->url.". Please specify. Available methods: $names");
         }

         //4. No appropriate method found
         $method_names = implode(', ', array_map(fn($m) => $m->getName(), $public_methods));
         throw new RuntimeException("No suitable action method found in class '$class_name' for HTTP verb '$http_verb' for the route '".$this->url."'. Public methods found: $method_names");
     }

     private function get_compiled_target(string $target, string $type = 'target'){
         $compiled_target = [null, null, null]; //controller, action, template_path

         //target must be a non empty string, otherwise complain loudly
         Assert::stringNotEmpty($target, 'Provide a non empty string for target for the route: '.$this->url);

         $target_array = explode("@", $target);
         $component_name = $target_array[0];

         //assert component exists
         $this->assert_components_exist([$component_name]);

         /**
          * a component can have at least a template or a controller(purely api components),
          * otherwise it can have both a template and a controller.
          * */
         $component = ComponentRegistry::get($component_name);
         $controller = $component['controller'];
         $template_path = $component['template_path'];

         if(!$controller && !$template_path){
             throw new InvalidArgumentException('Target must have at least a controller or a template for the route: '.$this->url);
         }

         //if there is a controller, ensure the class is defined and the action method provided exists
         if($controller){
             if(!class_exists($controller)){
                 throw new InvalidArgumentException('Target must have at least a controller or a template for the route: '.$this->url);
             }

             $compiled_target[0] = $controller;

             $action = $target_array[1] ?? '';

             //if the action has been provided at this point, ensure the method exists in class
             if($action && !method_exists($controller, $action)){
                 throw new InvalidArgumentException('The method ['.$action.'] does not exist in the class ['.$controller.'] for the route: '.$this->url);
             }

             //if the method still doesnt exist here, dynamically determine a method to use
             if(!$action){
                 $action = $this->find_appropriate_action($controller, $type);
             }

             $compiled_target[1] = $action;
         }

         //if there is a template_path, ensure the file exists
         if($template_path){
             if(!file_exists($template_path)){
                 throw new InvalidArgumentException('The template file: '.$template_path.' does not exist for the route: '.$this->url);
             }

             $extension = pathinfo($template_path, PATHINFO_EXTENSION);

             if (strtolower($extension) !== strtolower(COMPONENT_TEMPLATE_EXT)) {
                 throw new InvalidArgumentException("Invalid template file type for the route: ".$this->url." Expected an .".COMPONENT_TEMPLATE_EXT." file.");
             }

             $compiled_target[2] = $template_path;
         }

         return [$compiled_target, $component_name];
     }

     /**
      * For web requests, the route will declare which components
      * the final UI layout will be composed with
      * 
      * @param array $layouts: an array of components to compose the layout from. This can be an array of strings,
      * or an array of arrays of strings.
      * 
      * When an array of arrays of strings is provided, the resolver must be provided to determine
      * which layout group to use.
      * */
     public function compose_with(array $layouts, ?string $resolver = null): self {

         Assert::isNonEmptyList($layouts, 'Layouts array cannot be empty for route: '.$this->url);

         $is_flat   = true;
         $is_nested = true;

         foreach ($layouts as $item) {
             if (!is_string($item)){
                 $is_flat = false;
             }
             if (!is_array($item)) {
                 $is_nested = false;
             }
         }

         //Mixed or invalid structure
         if (!$is_flat && !$is_nested) {
             throw new InvalidArgumentException('Layouts must be either an array of strings or an array of arrays of strings for route: '.$this->url);
         }

         //CASE A: ['home', 'dashboard']
         if ($is_flat){

             Assert::allStringNotEmpty($layouts, 'Layout components must be non-empty strings for route: '.$this->url);

             if($resolver !== null){
                 throw new InvalidArgumentException('Resolver is not allowed for a single layout for route: '.$this->url);
             }

             $this->assert_components_exist($layouts);

             $this->layout = ['type' => 'static', 'layouts'=> [$layouts]];

             return $this;
         }

         //CASE B: [['admin','dashboard'], ['home','dashboard']]
         Assert::stringNotEmpty($resolver, 'A resolver name is required when multiple layouts are provided for route: '.$this->url);

         //Validate each layout
         foreach ($layouts as $layout){
             Assert::isArray($layout, 'Each layout must be an array for route: '.$this->url);
             Assert::allStringNotEmpty($layout, 'Layout components must be non-empty strings for route: '.$this->url);
         }

         //Ensure no duplicate layouts (order-sensitive)
         $seen = [];
         foreach ($layouts as $layout) {
             $key = implode('|', $layout);
             if (isset($seen[$key])) {
                 throw new InvalidArgumentException('Duplicate layout detected: [' . implode(', ', $layout) . '] for route: '.$this->url);
             }
             $seen[$key] = true;
         }

         //Flatten and validate component existence
         $all_components = array_unique(array_merge(...$layouts));
         $this->assert_components_exist($all_components);

         //Ensure resolver exists
         if (!Template::has($resolver, 'resolver')){
             throw new InvalidArgumentException("Layout resolver '{$resolver}' has not been registered for route: ".$this->url);
         }

         $this->layout = ['type' => 'dynamic', 'layouts' => $layouts, 'resolver' => $resolver];

         return $this;
     }

     private function assert_components_exist(array $components): void {
         foreach($components as $component){
             if(!ComponentRegistry::exists($component)){
                 throw new InvalidArgumentException("Layout component '{$component}' does not exist for route: ".$this->url);
             }
         }
     }

     /**
      * Add roles, permissions and attributes as the developer will have defined
      * in the AuthorizationProvider class that will determine whether the user 
      * is authorized to access this route or not
      * */
     public function requires(string $guard){
         $this->guards = [$guard];
         $this->guard_level = 'all';
         return $this;
     }

     public function requires_any(array $guards){
         $this->guards = $guards;
         $this->guard_level = 'any';
         return $this;
     }

     public function requires_all(array $guards){
         $this->guards = $guards;
         $this->guard_level = 'all';
         return $this;
     }

     /**
      * Set the response type from this route
      * */
     public function respond_with(array $restype){
         $this->restype = $restype;
         return $this;
     }
}
