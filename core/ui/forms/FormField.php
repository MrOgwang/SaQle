<?php
namespace SaQle\Core\Ui\Forms;

use RuntimeException;

class FormField {

     public private(set) array $attributes = [] {
         set(array $value){
             $this->attributes = $value;
         }

         get => $this->attributes;
     }

     public private(set) string $state = "default" {
         set(string $value){
             $this->state = $value;
         }

         get => $this->state;
     }

     public function __construct(array $attrs, string $state = 'default'){
         $this->attributes = $attrs;
         $this->state = $state;
     }

     public function __get($key){
        
         if(!array_key_exists($key, $this->attributes)){
             //throw new RuntimeException("Invalid form field attribute!");
             return "";
         }

         return $this->attributes[$key];
     }

     public function __set($key, $value){

         if(!array_key_exists($key, $this->attributes)){
             //throw new RuntimeException("Invalid form field attribute!");
             return;
         }

         $this->attributes = array_merge($this->attributes, [$key => $value]);
     }

}
