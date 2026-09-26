<?php

namespace SaQle\Lib\Components\FormField;

use SaQle\Core\Components\ComponentDefinition;
use SaQle\Http\Request\Request;
use SaQle\Core\Registries\ModelRegistry;
use SaQle\Core\Ui\Forms\{
     FormField as FormFieldContainer,
     FormFieldsCompiler
};

final class Definition extends ComponentDefinition {

     private function construct_field(string $field_key){

         $field_key_parts = explode(":", $field_key);

         $model_key = $field_key_parts[0];

         $field_name = $field_key_parts[1];

         $model_class = ModelRegistry::get_model_class($model_key);

         $form_fields = FormFieldsCompiler::compile($model_class, true);

         return $form_fields[$field_name] ?? null;
        
     }

     protected function init() : void { 

         //translate the bind prop into a FormField value

         if(array_key_exists("bind", $this->props)){

             $field = $this->construct_field($this->props['bind']);

             if(!$field){
                throw new RuntimeException("The field [".$this->props['bind']."] does not exist!");
             }

             /**
              * Override default field attributes with values
              * passed via props
              * */
             $default_attrs = $field->get_attributes();

             //do not override these attributes
             $unsafe_attrs = ['name', 'id'];

             foreach($default_attrs as $attr => $attr_val){
                 if(array_key_exists($attr, $this->props) && !in_array($attr, $unsafe_attrs)){
                     $field->$attr($this->props[$attr]);
                 }
             }

             $field->value($field->default);

             $this->props['field'] = $field;
         }
         /**
          * There should be a field prop provided here,
          * if not, provision a default one
          * */
         else{

             $field = $this->props['field'] ?? null;
             
             if(!$field || !$field instanceof FormFieldContainer){
                 
                 $this->props['field'] = new FormFieldContainer([
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
     }

     private function make_file_name(string $type) : string {

         $file_name = ucwords(str_replace(['_', '-'], " ", $type));

         $file_name = str_replace(" ", "", $file_name);

         return $file_name;
     }

     public function template(Request $request): ?string {

         $type = $this->props['field']->type;

         $name = $this->make_file_name($type);

         $path = path_join([
             $this->path('Templates'),
             $name,
             "Template.".config('app.component_template_ext')
         ]);

         if(file_exists($path)){
             return $name;
         }

         return match($type){
             'datetime-local' => 'DateTimeLocal',
             'textarea'       => 'TextArea',
             default          => null
         };
     }
}