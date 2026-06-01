<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Forms;

use SaQle\Core\Ui\Forms\Fields\FormField;
use SaQle\Auth\Models\GuestUser;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Core\Registries\FormBlueprintRegistry;
use SaQle\Core\Assert\Assert;

final class Form {

     private FormRuntimeContext $context;

     public string $form_name {
         set(string $value){
             $this->form_name = $value;
         }

         get => $this->form_name;
     }

     public string $mode {
         set(string $value){
             $this->mode = $value;
         }

         get => $this->mode;
     }

     public string $model_name {
         set(string $value){
             $this->model_name = $value;
         }

         get => $this->model_name;
     }

     public string $model_class {
         set(string $value){
             $this->model_class = $value;
         }

         get => $this->model_class;
     }

     public string $method {
         set(string $value){
             $this->method = $value;
         }

         get => $this->method;
     }

     public bool $auto_wire = false {
         set(bool $value){
             $this->auto_wire = $value;
         }

         get => $this->auto_wire;
     }

     public array $fields = [];

     public private(set) array $all_fields {
         set(array $value){
             $this->all_fields = $value;
         }

         get => $this->all_fields;
     }

     /**
      * FILLABLE
      * 
      * A list of field names whose controls
      * will be created on the form.
      *
      * */
     public array $fillable = [] {
         set(array $value){
             Assert::isNonEmptyList($value, 'Please provide a list of field names to fill!');
             
             $this->fillable = $value;

             if($this->fillable){
                 $fillable_fields = [];
                 foreach($this->fillable as $field_name){
                     $fillable_fields[$field_name] = $this->all_fields[$field_name];
                 }

                 $this->fields = $fillable_fields;
             }
         }

         get => $this->fillable;
     }

     public static function make_from_model(
         string $model_class, 
         string $method, 
         ?string $model_name = null,
         ?string $form_name = null
     ) : static {
         $form = new static();

         $form->form_name = $form_name;
         $form->model_name = $model_name;
         $form->model_class = $model_class;
         $form->method = $method;
         $form->all_fields = FormFieldsCompiler::compile($form->model_class);
         $form->fields = $form->all_fields;

         /**
          * Call the model form method to
          * customize some things
          * */
         $model_instance = $model_class::make();
         $form = $model_instance->$method($form);

         return $form;
     }

     public static function make_from_name(string $form_name) : static {
         [$model_name, $model_class, $method] = FormModelResolver::resolve($form_name);
         return self::make_from_model($model_class, $method, $model_name, $form_name);
     }

     public function bind(FormRuntimeContext $context): void {
         $this->context = $context;
     }
}