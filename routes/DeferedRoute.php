<?php
/**
 * A route object
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use SaQle\Core\Assert\Assert;

final class DeferedRoute {

     public private(set) string $key = "" {
         set(string $value){
             $this->key = $value;
         }

         get => $this->key;
     }

     public private(set) string $method {
         set(string $value){
             $this->method = $value;
         }

         get => $this->method;
     }

     public private(set) string $url {
         set(string $value){
             $this->url = $value;
         }

         get => $this->url;
     }

     public private(set) array $routes {
         set(array $value){
             $this->routes = $value;
         }

         get => $this->routes;
     }

     public private(set) string $resolver {
         set(string $value){
             $this->resolver = $value;
         }

         get => $this->resolver;
     }

     public function __construct(string $method, string $url, array $routes, string $resolver){
         $this->method = $method;
         $this->url = $url;
         $this->routes = $routes;
         $this->resolver = $resolver;
     }

     public function set_key(string $key){
         $this->key = $key;
     }
}
