<?php

namespace SaQle\Core\Support;

use Attribute;
use SaQle\Http\Response\ResponseType;
use SaQle\Routes\Router;
use RuntimeException;

#[Attribute(Attribute::TARGET_METHOD)]
class Route {

	 public private(set) string $name {
         set(string $value){
             $this->name = $this->validate_property('name', $value);
         }

         get => $this->name;
     }

     public private(set) string $method {
         set(string $value){
         	 $value = $this->validate_property('method', $value);

         	 if(!in_array($value, ['get', 'put', 'post', 'patch', 'delete'])){
         	 	 throw new RuntimeException("Invalid http method provided for route: {$this->url}");
         	 }

             $this->method = $value;
         }

         get => $this->method;
     }

     public private(set) string $url {
         set(string $value){
             $this->url = $this->validate_property('url', $value);
         }

         get => $this->url;
     }

     private string $target;

     private string $guards;

     private array $layout;

     private ?string $model = null;

     public function __construct(
     	 string $name,
     	 string $method,
     	 string $url,
     	 string $guards = "",
     	 array $layout = [],
         ?string $model = null
     ){
     	 $this->name = $name;
     	 $this->method = $method;
     	 $this->url = $url;
     	 $this->guards = trim($guards);
     	 $this->layout = $layout;
         $this->model = $model;
     }

     private function validate_property(string $prop, string $value) : string {
     	 $value = trim($value);

     	 if(!$value){
     	 	 throw new RuntimeException("Route property [{$prop}] missing or empty!");
     	 }

     	 return strtolower($value);
     }

     public function set_target(string $target){
     	 $this->target = $target;
     }

     public function initialize(){

     	 $method = $this->method;
     	 $router = Router::$method($this->url, $this->target, $this->model)->name($this->name);

     	 if($this->guards){
     	 	 $router->requires($this->guards);
     	 }

     	 if($this->layout){
     	 	 $router->compose_with($this->layout);
     	 }
     }
}