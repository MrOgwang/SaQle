<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Forms;
 
use SaQle\Core\Registries\ModelRegistry;

final class Form {

     //form name as declared in the model
     private string $name;

     //form mode: create or update
     private ?string $mode = null;

     //the runtime context for form
     private ?FormContext $context = null;

     //form description
     private string $_description = "";

     //the class of the model associated with form
     private ?string $model_class = null;

     //whether to process form submission automatically
     private bool $wire = false;

     //all form fields. This to be used internally
     private array $fields_register = [];

     //final fields to be shown on form
     private array $fields = [];

     public function __construct(string $name, string $model_class, array $form_fields){
         $this->name = $name;
         $this->model_class = $model_class;
         $this->fields_register = $form_fields;
     }

     //custom form modes
     public function for_create(){
         $this->mode = 'create';

         return $this;
     }

     public function for_update(){
         $this->mode = 'update';

         return $this;
     }

     public function is_for_create(){
         return $this->mode === 'create';
     }

     public function is_for_update(){
         return $this->mode === 'update';
     }

     public function get_mode(){
         return $this->mode;
     }

     //runtime context api
     public function bind(FormContext $context){

         $model_data = $context->model ? $context->model->get_data() : [];

         foreach($this->fields as $field){

             $value = $field->default;

             if(is_array($context->input) && array_key_exists($field->name, $context->input)){
                 $value = $context->input[$field->name];
             }elseif(array_key_exists($field->name, $model_data)){
                 $value = $model_data[$field->name];
             }

             $field->value($value);

             if(is_array($context->errors) && array_key_exists($field->name, $context->errors)){
                 $field->errors($context->errors[$field->name]);
             }
             
         }

         $this->context = $context;

         return $this;
     }

     public function get_context(){
         return $this->context;
     }

     //form description
     public function description(string $desc){
         $this->_description = $desc;

         return $this;
     }

     public function get_description() : string {
         return $this->_description;
     }

     public function get_model() : string {
         return $this->model_class;
     }

     public function get_model_name() : string {
         return ModelRegistry::get_model_name($this->model_class);
     }

     //form auto wiring
     public function auto_wire(){
         $this->wire = true;

         return $this;
     }

     public function should_auto_wire(){
         return $this->wire;
     }

     //fields to be filled
     public function get_fields(){
         return $this->fields;
     }

     public function remove_fields_register(){
         $this->fields_register = [];
     }

     private function decorate_field(FormField $field) : FormField {
         $new_field = unserialize(serialize($field));

         $new_field->data('fname', $this->name);
         $new_field->data('mode', $this->mode);
         $new_field->data('model', ModelRegistry::get_long_model_name($this->model_class));
         
         if($new_field->is_relation()){
             $new_field->class('opts_deferred');
         }  

         return $new_field;
     }

     public function fill_all(){
         $this->fields = [];

         foreach($this->fields_register as $field_name => $field){
             $this->fields[$field_name] = $this->decorate_field($field);
         }

         return $this;
     }

     public function fill(array $fields){

         $fillable_fields = [];

         foreach($fields as $field_name){
             if(isset($this->fields_register[$field_name])){
                 $field = $this->decorate_field($this->fields_register[$field_name]);
                 $fillable_fields[$field_name] = $field;
             }
         }

         $this->fields = $fillable_fields;

         return $this;
     }

     public function exclude(array $fields){

         $fillable_fields = [];

         foreach($this->fields_register as $field_name => $field){
             if(!in_array($field_name, $fields)){
                 $fillable_fields[$field_name] = $this->decorate_field($field);
             }
         }

         $this->fields = $fillable_fields;

         return $this;
     }

     //customize form fields
     public function field(string $name){
         return $this->fields[$name] ?? null;
     }

}