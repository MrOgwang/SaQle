<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Forms;

use SaQle\Auth\Models\GuestUser;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Core\Registries\FormBlueprintRegistry;
use SaQle\Core\Assert\Assert;

final class Form {

     //form mode: create or update
     private ?string $mode = null;

     //the runtime context for form
     private ?FormRuntimeContext $context = null;

     //form description
     private string $_description = "";

     //the name of the form as used in the ui:form tag
     private string $_name = "";

     //the name of the model associated with form.
     private ?string $model_name = null;

     //the class of the model associated with form
     private ?string $model_class = null;

     //whether to process form submission automatically
     private bool $wire = false;

     //all form fields. This to be used internally
     private array $all_fields = [];

     //final fields to be shown on form
     private array $fields = [];
     
     /**
      * The name of the method on the model class
      * that will be called to customize this form
      * */
     private ?string $_customizer = null;


     public function __construct(string $model_class, ?string $model_name = null){
         $this->model($model_name, $model_class);
         $this->bind(FormRuntimeContext::from_session());
         $this->all_fields = FormFieldsCompiler::compile($this->model_class, $this->context);
         $this->fill_all();
     }

     //create new form apis
     public static function make_from_model(
         string $model_class, 
         string $method, 
         ?string $model_name = null,
         ?string $form_name = null
     ) : static {

         $form = new static($model_class, $model_name);
         $form->name($form_name);
         $form->customizer($method);
        
         $model_instance = $model_class::make();
         $form = $model_instance->$method($form);

         return $form;
     }

     public static function make_from_name(string $form_name) : static {
         [$model_name, $model_class, $method] = FormModelResolver::resolve($form_name);
         return self::make_from_model($model_class, $method, $model_name, $form_name);
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
     public function bind(FormRuntimeContext $context) {
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

     //form name
     public function name(string $name){
         $this->_name = $name;

         return $this;
     }

     public function get_name() : string {
         return $this->_name;
     }

     //form model
     public function model(string $name, string $class){
         $this->model_name = $name;
         $this->model_class = $class;

         return $this;
     }

     public function get_model_name() : string {
         return $this->model_name;
     }

     public function get_model_class() : string {
         return $this->model_class;
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

     public function fill_all(){
         $this->fields = [];

         foreach($this->all_fields as $field_name => $field){
             $this->fields[$field_name] = $field;
         }

         return $this;
     }

     public function fill(array $fields){

         $fillable_fields = [];

         foreach($fields as $field_name){
             if(isset($this->all_fields[$field_name])){
                 $fillable_fields[$field_name] = $this->all_fields[$field_name];
             }
         }

         $this->fields = $fillable_fields;

         return $this;
     }

     public function exclude(array $fields){

         $fillable_fields = [];

         foreach($this->all_fields as $field_name => $field){
             if(!in_array($field_name, $fields)){
                 $fillable_fields[$field_name] = $field;
             }
         }

         $this->fields = $fillable_fields;

         return $this;
     }

     //the method name that customizes the form
     public function customizer(string $customizer){
         $this->_customizer = $customizer;

         return $this;
     }

     public function get_customizer() : string {
         return $this->_customizer;
     }

     //customize form fields
     public function field(string $name){
         return $this->fields[$name];
     }

}