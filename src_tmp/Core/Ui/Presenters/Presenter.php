<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Presenters;
 
use SaQle\Core\Registries\ModelRegistry;
use SaQle\Http\Request\Request;
use RuntimeException;

final class Presenter {

     //form name as declared in the model
     private string $name;

     //all model fields. This to be used internally
     private array $fields_register = [];

     //render methods for fields
     private array $fields = [];

     public function __construct(string $name, array $model_fields){
         $this->name = $name;
         $this->fields_register = $model_fields;
     }

     public function field(string $field_name, mixed $renderer){
        
         if(!array_key_exists($field_name, $this->fields)){
             throw new RuntimeException("The field: {$field_name} has not been included in the presenter: {$this->name}");
         }

         $this->fields[$field_name] = $renderer;
     }

     public function get_fields() : array {
         return $this->fields;
     } 

     public function get_field(string $name) : mixed {
         return $this->fields[$name] ?? null;
     }

     public function show_all(){

         $this->fields = [];

         foreach($this->fields_register as $fn){
             $this->fields[$fn] = null;
         }
     }

     public function show(array $field_names){
        
         $this->fields = [];

         foreach($field_names as $fn){
             if(in_array($fn, $this->fields_register)){
                 $this->fields[$fn] = null;
             }
         }
     }

     public function exclude(array $field_names){
        
         $this->fields = [];

         foreach($this->fields_register as $fn){
             if(!in_array($fn, $field_names)){
                 $this->fields[$fn] = null;
             }
         }
     }

}