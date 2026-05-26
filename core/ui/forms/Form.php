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

     public static function make(string $form_name) : static {
         [$model_name, $model_class, $method] = FormModelResolver::resolve($form_name);

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

     public function bind(FormRuntimeContext $context): void {
         $this->context = $context;
     }






     public function render(): string {
         $html = '<form method="'.$this->context->method.'" action="'.$this->context->action.'">';

         $context = ['session_user' => request()->user ?? new GuestUser()];

         foreach($this->blueprint->fields as $field_def){

             $control_attrs = $field_def['control_attrs'];

             if (isset($this->context->input[$field_def['name']])) {
                 $control_attrs['value'] = $this->context->input[$field_def['name']];
             }

             $field = new FormField(
                 control_attributes: $control_attrs,
                 field_attributes: array_merge($context, $field_def['field_attrs']),
                 state: request()->method() === 'GET' ? 'default' : ($this->context->errors ? 'invalid' : 'valid')
             );

             $html .= $field->render([
                 'error' => $this->context->errors[$field_def['name']] ?? null
             ]);
         }

         $html .= '</form>';

         return $html;
     }
}