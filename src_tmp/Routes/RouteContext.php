<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The route context accumulates shared context to be later
 * pushed to individual routes
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use RuntimeException;

final class RouteContext {

     protected array $attributes = [
         'middleware' => [],
         'layout'     => [],
         'authorize'  => null,
         'prefix'     => '',
         'name'       => '',
         'url'        => '',
         'target'     => ''
     ];

     public function __construct(
         private bool $is_group = true
     ){}

     public function middleware(array $middleware): self {
         $this->attributes['middleware'] = array_merge(
             $this->attributes['middleware'],
             $middleware
         );

         return $this;
     }

     public function authorize(string $guard): self {
         $this->attributes['authorize'] = $guard;
         return $this;
     }

     public function layout(array $layout): self {
         $this->attributes['layout'] = $layout;
         return $this;
     }

     public function prefix(string $prefix): self {
         $this->attributes['prefix'] = $prefix;
         return $this;
     }

     public function name(string $name): self {
         $this->attributes['name'] = $name;
         return $this;
     }

     public function url(string $url): self {

         if($this->is_group){
             throw new RuntimeException('Cannot call url() on non-route context!');
         }

         $this->attributes['url'] = $url;

         return $this;
     }

     public function target(string $target): self {

         if($this->is_group){
             throw new RuntimeException('Cannot call target() on non-route context!');
         }

         $this->attributes['target'] = $target;
         return $this;
     }

     private function process_context($callback){
         Router::register_context($this->attributes);

         try{
             $callback();
         }finally{
             Router::remove_context();
         }
     }

     public function routes(callable $callback): void {

         if(!$this->is_group){
             throw new RuntimeException('Cannot call routes on non-group context!');
         }
         
         $this->process_context($callback);
     }

     public function methods(callable $callback) : void {
         
         if($this->is_group){
             throw new RuntimeException('Cannot call methods on non-route context!');
         }

         $this->process_context($callback);
     }
}