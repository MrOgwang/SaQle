<?php

namespace SaQle\Core\Support;

use ReflectionClass;
use SaQle\Auth\Exceptions\AuthorizationException;
use SaQle\Core\Exceptions\ValidationException;
use SaQle\Core\Ui\Forms\{
     Form,
     FormFieldsCompiler,
     FormContext
};

abstract class RequestContract {

     protected array $validated_data = [];

     final public function validate_and_authorize(): void {
         if(!$this->authorize()) {
             throw new AuthorizationException('This action is unauthorized.');
         }

         $this->before_validation();

         $this->perform_validation();

         $this->after_validation();
     }

     final public function validated(): array {
         return $this->validated_data;
     } 

     abstract protected function authorize(): bool;

     protected function perform_validation(): void {

         $errors = [];
         $data = [];

         $reflection = new ReflectionClass($this);

         foreach($reflection->getProperties() as $property){

             $type = $property->getType();

             $validation_attr = $property->getAttributes(Validation::class);
             $mapping_attr = $property->getAttributes(MapTo::class);

             if(!$validation_attr && $mapping_attr){
                 continue;
             } 

             $validation_instance = $validation_attr ? $validation_attr[0]->newInstance() : null;
             $mapping_instance    = $mapping_attr ? $mapping_attr[0]->newInstance() : null;

             $property_name = $property->getName();
             $value         = $this->$property_name ?? null;
             $optional      = $type?->allowsNull() ?? false;

             //combine custom and inherited rules:

             $mapping_rules = [];
             $custom_rules  = [];

             if($mapping_instance){
                 
                 if(!$mapping_instance->field){
                     $mapping_instance->field($property_name);
                 }

                 $mapping_rules = $mapping_instance->get_validation_rules();
                 
             }

             if($validation_instance){

                 $custom_rules = RuleParser::parse($validation_instance->rules ?? []);

                 if(!$validation_instance->inherit){
                     $mapping_rules = [];
                 }else{
                     if(!empty($validation_instance->only)){
                         $mapping_rules = array_intersect_key($mapping_rules, array_flip($validation_instance->only));
                     }elseif(!empty($validation_instance->except)){
                         $mapping_rules = array_diff_key($mapping_rules, array_flip($validation_instance->except));
                     }
                 }
             }

             $rules = array_merge($mapping_rules, $custom_rules);
             
             /**
              * Validate only non optional properties or
              * optional properties for which values have been provided
              * */
             if(!$optional || ($optional && !is_null($value))){

                 $validator = new FieldValidator(rules: $rules, array: false);

                 $result = $validator->validate($property_name, $value);

                 if($result->isvalid){
                     $this->validated_data[$property_name] = $result->normalized;
                 }else{
                     $errors[$property_name] = $result->errors;
                 }

                 $data[$property_name] = $value;
             }else{
                 $this->validated_data[$property_name] = $value;
             }
         }

         if(!empty($errors)){
             throw new ValidationException(context: [
                 'errors' => $errors,
                 'input' => $data
             ]);
         }
     }

     //called before data enters validation
     protected function before_validation(){
         //do nothing
     }

     //called after is validated
     protected function after_validation(){
         //do nothing
     }

     public function get_fields(){
        
         return FieldExtractor::extract($this::class);

     }

     public function form(string $form_name, ?object $model = null){

         $form_fields = FormFieldsCompiler::compile($this::class);
         
         $form = new Form($form_name, $this::class, $form_fields);
         $form->fill_all();

         if($model){
             $form->for_update();
             $form->bind(FormContext::make($model));
         }else{
             $form->for_create();
             $form->bind(FormContext::make());
         }

         return $form;

     }
}