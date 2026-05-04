<?php
/**
 * A route object
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use SaQle\Core\Assert\Assert;
use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Http\Request\RequestScope;
use SaQle\Http\Response\ResponseType;
use InvalidArgumentException;
use RuntimeException;

final class Route {

     public private(set) ?string $key = null {
         set(?string $value){
             $this->key = $value;

             if(is_null($this->name)){
                 $this->name = $value;
             }
         }

         get => $this->key;
     }

     //scope of route
     public private(set) ?RequestScope $scope = null {
         set(?RequestScope $value){ 
             $this->scope = $value;
         }

         get => $this->scope;
     }

     //name of route
     public private(set) ?string $name = null {
         set(?string $value){
             $this->name = $value;
         }

         get => $this->name;
     }

     //if this is an event stream route, customize the event info here
     public private(set) ?array $sse_event = null {
         set(?array $value){
             $this->sse_event = $value;
         }

         get => $this->sse_event;
     }

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
      * componentname         - just the component name, 
      * the method to excute will be determined automatically if a component has a controller
      * 
      * */
     public string $target {
         set(string $value){

             if($value){
                 $compiled_target = ComponentRegistry::resolve_component($value, $this->method, 'target');

                 $this->compiled_target = $compiled_target;

                $this->target = !is_null($compiled_target[2]) ? $compiled_target[0]."@".$compiled_target[2] : $compiled_target[0];
             }
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

     //customize the response from this route
     public ?ResponseType $restype = null;

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
         $this->scope = RequestScope::WEB;
	 }

     public function target(string $target){
         $this->target = $target;
         return $this;
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
         
         $this->layout = $layouts;

         return $this;
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

     public function respond_with(ResponseType $restype){
         $this->restype = $restype;
         return $this;
     }

     /**
      * Customize event meta data for event stream
      * routes.
      * 
      * @var string event - the name of event
      * @var int interval - the interval for sleep
      * */
     public function sse(string $event, int $interval){
         $meta = [
             'event' => $event,
             'interval' => $interval
         ];

         $this->sse_event = $meta;

         return $this;
     }

     public function set_name(string $name){
         $this->name = $name;
     }

     public function name(string $name){
         $this->name = $name;
         return $this;
     }

     public function set_scope(RequestScope $scope){
         $this->scope = $scope;
     }

     public function set_key(string $key){
         $this->key = $key;
     }
}
