<?php

namespace SaQle\Lib\Components\FormField;

use SaQle\Core\Components\Abstracts\AbstractDefinition;
use SaQle\Http\Request\Request;
use SaQle\Core\Registries\ModelRegistry;
use SaQle\Core\Ui\Forms\{
     FormField as FormFieldContainer,
     FormFieldsCompiler
};

final class Definition extends AbstractDefinition {

     private function construct_field(string $field_key){

         $field_key_parts = explode(":", $field_key);

         $model_key = $field_key_parts[0];

         $field_name = $field_key_parts[1];

         $model_class = ModelRegistry::get_model_class($model_key);

         $form_fields = FormFieldsCompiler::compile($model_class, true);

         return $form_fields[$field_name] ?? null;
        
     }

     protected function initialize(array $props = []) : void { 

         parent::initialize($props);

         $bind = $this->context()->props()->get('bind', null);

         $field = null;

         $not_bound = false;

         if($bind !== null){

             //translate the bind prop into a FormField value
             $field = $this->construct_field($bind);
         }
         /**
          * There should be a field prop provided here,
          * if not, provision a default one
          * */
         else{

             $field = $this->context()->props()->get('field', null);

             if(!$field || !$field instanceof FormFieldContainer){

                 $not_bound = true;

                 $field = new FormFieldContainer([
                     'id'          => '',
                     'label'       => '',
                     'name'        => '',
                     'helper_text' => '',
                     'value'       => '',
                     'errors'      => [],
                     'required'    => false,
                     'type'        => 'text',
                     'placeholder' => "",
                 ]);
             }
         }

         if(!$field){
             throw new RuntimeException("Could not resolve field component!");
         }

         /**
          * Override default field attributes with values
          * passed via props
          * */
         $default_attrs = $field->get_attributes();

         //do not override these attributes
         $unsafe_attrs =  $not_bound ? [] : ['name', 'id'];

         foreach($default_attrs as $attr => $attr_val){
             if($this->context()->props()->exists($attr) && !in_array($attr, $unsafe_attrs)){
                 $field->$attr($this->context()->props()->get($attr));
             }
         }

         $field->value($field->default);

         $this->context()->props()->set('field', $field);
     }

     private function make_file_name(string $type) : string {

         $file_name = ucwords(str_replace(['_', '-'], " ", $type));

         $file_name = str_replace(" ", "", $file_name);

         return $file_name;
     } 

     public function template(Request $request): ?string {

         $field = $this->context()->props()->get_or_fail('field');

         $name = $this->make_file_name($field->type);

         $path = path_join([ 
             $this->path('Templates'),
             $name,
             "Template.".config('app.component_template_ext')
         ]);

         if(file_exists($path)){
             return $name;
         }

         return match($field->type){
             'datetime-local' => 'DateTimeLocal',
             'textarea'       => 'TextArea',
             default          => null
         };
     }
}