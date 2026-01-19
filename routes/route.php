<?php
/**
 * A route object
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use SaQle\Core\Assert\Assert;
use SaQle\Core\Registries\ComponentRegistry;
use InvalidArgumentException;
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

             $this->trail = $this->construct_layout_trail();
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
             $compiled_target = ComponentRegistry::resolve_component($value, $this->method, 'target');

             $this->compiled_target = $compiled_target;

             $this->target = !is_null($compiled_target[2]) ? $compiled_target[0]."@".$compiled_target[2] : $compiled_target[0];
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

             $this->trail = $this->construct_layout_trail();
         }

         get => $this->layout;
     }

     /**
      * These are roles, permissions and attributes as the developer will have defined
      * in the AuthorizationProvider class that will determine whether the user 
      * is authorized to access this route or not
      * 
      * each entry into the guards takes the structure below
      * 
      * [mode: 'all', guards: ['guard1', 'guard2']]
      * 
      * */
     public ?array $guards = null{
         set(?array $value){
             $this->guards = $value;
         }

         get => $this->guards;
     }

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

     /**
      * The trail is an array of the components and actions
      * that will be used to construct the final layout
      *
      * */
     public ?array $trail = null {
         set(?array $value){
             $this->trail = $value;
         }

         get => $this->trail;
     }

     /**
      * The model class name for 
      * a resource route
      *
      * */
     public ?string $model_class = null {
         set(?string $value){
             $this->model_class = $value;
         }

         get => $this->model_class;
     }

     //create a new route object
	 public function __construct(string $method, string $url, string $target, ?string $model_class = null){
         $this->method = $method;
         $this->url    = $url;
         $this->target = $target;
         $this->model_class = $model_class;

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
     public function compose_with(array $layouts): self {

         Assert::allStringNotEmpty($layouts, 'Layout components must be non-empty strings for route: '.$this->url);

         ComponentRegistry::assert_components_exist($layouts);

         $layouts = array_unique(array_merge($this->layout ?? [], $layouts));

         //array_unshift($layouts, 'page');

         $this->layout = $layouts;

         return $this;
     }

     private function construct_layout_trail(){
         
         $page_compiled_target = ComponentRegistry::resolve_component('page', $this->method, 'layout');
         $compiled_targets = [];
         $trail_components = [];

         if($this->layout){
             
             foreach($this->layout as $c) {
                 $ct = ComponentRegistry::resolve_component($c, $this->method, 'layout');
                 $trail_components[] = $ct[0];
                 $compiled_targets[] = $ct;
             }

         }

         //add the targets compiled target
         $compiled_targets[] = $this->compiled_target;
         $trail_components[] = $this->compiled_target[0];

         //push in the page component
         array_unshift($trail_components, $page_compiled_target[0]);
         array_unshift($compiled_targets, $page_compiled_target);

         return [implode('.', $trail_components) => $compiled_targets];
     }

     /**
      * Add roles, permissions and attributes as the developer will have defined
      * in the AuthorizationProvider class that will determine whether the user 
      * is authorized to access this route or not
      * */

     private function register_guards(array $guards, string $mode){
         $current_guards = $this->guards ?? [];
         $current_guards[] = ['mode' => $mode, 'guards' => $guards];
         $this->guards = $current_guards;
     }

     public function requires(string $guard){
         $this->register_guards([$guard], 'all');
         return $this;
     }

     public function requires_any(array $guards){
         $this->register_guards($guards, 'any');
         return $this;
     }

     public function requires_all(array $guards){
         $this->register_guards($guards, 'all');
         return $this;
     }

     /**
      * Set the response type from this route
      * */
     public function respond_with(array $restype){
         $this->restype = array_unique(array_merge($this->restype ?? [], $restype));
         return $this;
     }
}
