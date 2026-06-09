<?php
namespace SaQle\Core\Ui\Forms;

use RuntimeException;

class FormField {

     private array $attributes = [];

     public private(set) string $state = "default" {
         set(string $value){
             $this->state = $value;
         }

         get => $this->state;
     }

     public private(set) string $ui_type = "normal" {
         set(string $value){
             $this->ui_type = $value;
         }

         get => $this->ui_type;
     }

     public function __construct(
         array  $attrs, 
         string $ui_type = 'normal', 
         string $state = 'default'
     ){
         $this->attributes = $attrs;
         $this->state = $state;
         $this->ui_type = $ui_type;
     }

     public function __get($key){
        
         if(!array_key_exists($key, $this->attributes)){
             //throw new RuntimeException("Invalid form field attribute!");
             return "";
         }

         return $this->attributes[$key];
     }

     public function __call($method, $args) {
         //fluent setter
         if(count($args) === 1){
             $this->attributes[$method] = $args[0];
             
             return $this;
         }

         //boolean flags
         if(count($args) === 0){
             $this->attributes[$method] = true;

             return $this;
         }

         return $this;
     }

     public function get_attributes(){
         return $this->attributes;
     }
}
