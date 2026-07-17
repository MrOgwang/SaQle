<?php
/**
 * A route object
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use SaQle\Core\Assert\Assert;
use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Http\Request\RequestScope;
use SaQle\Core\Ui\UiLayout;
use SaQle\Auth\Guards\GuardParser;
use InvalidArgumentException;
use RuntimeException;

final class Route {

     public private(set) ?string $key = null {
         set(?string $value){
             $this->key = $value;
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

     //route prefix
     public private(set) ?string $prefix = null {
         set(?string $value){
             $this->prefix = $value;
         }

         get => $this->prefix;
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
                
                 ComponentRegistry::reload(true);
                 $compiled_target = ComponentRegistry::resolve_component($value, $this->method, 'target');

                 $this->compiled_target = $compiled_target->to_array();

                 $this->target = $compiled_target->get_target();
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
      * Route middleware
      * */
     public ?array $middleware = null {
         set(?array $value){
             $this->middleware = $value;
         }

         get => $this->middleware;
     }

     /**
     * These are roles, permissions and attributes as the developer will have defined
     * in the AuthorizationProvider class that will determine whether the user 
     * is authorized to access this route or not
     * 
     * Examples:
     *
     *  authenticated
     *
     *  authenticated && admin
     *
     *  authenticated && (admin || manager)
     *
     *  authenticated && !banned
     *
     *  (admin || moderator) && verified
     * 
     * */
     private string $guard = "" {
         set(string $value){
             $this->guard = $value;
             $this->guards = new GuardParser()->parse($value);
         }

         get => $this->guard;
     }

     //parsed guards
     public array $guards = [];

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
         $this->method      = $method;
         $this->url         = $url;
         $this->target      = $target;
         $this->model_class = $model_class;
         $this->scope       = RequestScope::WEB;
         $this->key         = substr(hash('xxh128', $method.$url), 0, 16);
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
      * @param array $layouts: an array of components to compose the layout from.
      * 
      * Layouts are appended to already set layouts
      * */
     public function layout(array $layouts): self {

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
      * 
      * Guards are appended to previous set guards
      * */
     public function authorize(string $guard){

         $guard = trim($guard);

         if(!$guard){
             throw new RuntimeException("Guard cannot be an empty!");
         }

         if(!str_starts_with($guard, "(")){
             $guard = "(".$guard;
         }

         if(!str_ends_with($guard, ")")){
             $guard = $guard.")";
         }

         $current_guard = $this->guard;
         if(!$current_guard){
             $this->guard = $guard;
             return $this;
         }

         $this->guard = $current_guard." && ".$guard;

         return $this;
     }

     /**
      * Scope overrides previsously set scope
      * */
     public function scope(RequestScope $scope){
         $this->scope = $scope;

         return $this;
     }

     /**
      * Customize event meta data for event stream
      * routes.
      * 
      * @var string event - the name of event
      * @var int interval - the interval for sleep
      * 
      * Overrides previously set sse meta data
      * */
     public function sse(string $event, int $interval){
         $meta = [
             'event' => $event,
             'interval' => $interval
         ];

         $this->sse_event = $meta;

         return $this;
     }

     /**
      * Appends to already existing name
      * */
     public function name(string $name){

         if(!trim($name)){
             return $this;
         }

         $this->name = $this->name ? trim($this->name.".".$name) : $name;
         
         return $this;
     }

     /**
      * Appends to already existing prefix
      * */
     public function prefix(string $prefix){
         
         if(!trim($prefix)){
             return $this;
         }

         $this->prefix = $this->prefix ? trim($this->prefix."/".$prefix) : $prefix;

         $this->url = url_join([$this->prefix, $this->url]);
         
         return $this;
     }

      /**
       * Middleware is appended to already existing middleware
       * */
      public function middleware(array $middleware): self {

         $middleware = array_unique(array_merge($this->middleware ?? [], $middleware));
         
         $this->middleware = $middleware;

         return $this;
     }
}
