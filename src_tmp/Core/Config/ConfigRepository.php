<?php

namespace SaQle\Core\Config;

use Closure;

final class ConfigRepository {
    
     private array $items = [];
     private array $stack = [];

     public function __construct(array $items){
         $this->items = $items;
     }

     public function get(?string $key = null, mixed $default = null) : mixed {
         if(!$key){
             return $this->items;
         }

         $segments = explode('.', $key);

         $value = $this->items;

         foreach($segments as $segment){
             if(!is_array($value) || !array_key_exists($segment, $value)){
                 return $default;
             }

             $value = $value[$segment];
         }

         return $value;
     }

     public function set(string $key, mixed $value): void {
         $segments = explode('.', $key);

         $current =& $this->items;

         foreach ($segments as $segment) {
             if(!isset($current[$segment]) || !is_array($current[$segment])) {
                 $current[$segment] = [];
             }

             $current =& $current[$segment];
         }

         $current = $value;
     }

     public function has(string $key): bool {
         $segments = explode('.', $key);

         $value = $this->items;

         foreach($segments as $segment){
             if(!is_array($value) || !array_key_exists($segment, $value)){
                 return false;
             }

             $value = $value[$segment];
         }

         return true;
     }

     public function all(): array {
         return $this->items;
     }

     public function namespace(string $prefix): array{
         return array_filter(
             $this->items,
             fn($k) => str_starts_with($k, $prefix),
             ARRAY_FILTER_USE_KEY
         );
     }

     public function push(array $overrides): void {
         $this->stack[] = $this->items;
         $this->items = array_replace($this->items, $overrides);
     }

     public function pop(): void {
         $this->items = array_pop($this->stack);
     }

     public function merge(array $config): void {
         $this->items = array_replace_recursive($this->items, $config);
     }

     public function resolve_references(): void {
         $this->items = $this->resolve_array($this->items);
     }

     private function resolve_array(array $items): array {

         foreach($items as $key => $value){
             if(is_array($value)){
                 $items[$key] = $this->resolve_array($value);
                 continue;
             }

             if(is_string($value)){
                 $items[$key] = $this->resolve_string($value);
             }
         }

         return $items;
     }

     private function resolve_string(string $value): string {
         return preg_replace_callback(
             '/\$\{([^}]+)\}/',
             function($matches){
                 $replacement = $this->get($matches[1]);
                 return $replacement ?? '';
             },
             $value
         );
     }

     public function resolve_closures(): void {
         $this->items = $this->resolve_closure_array($this->items);
     }

     private function resolve_closure_array(array $items): array {
         foreach($items as $key => $value){
             if(is_array($value)){
                 $items[$key] = $this->resolve_closure_array($value);
                 continue;
             }
 
             if($value instanceof Closure) {
                 $items[$key] = $value($this);
             }
         }

         return $items;
     }
}
