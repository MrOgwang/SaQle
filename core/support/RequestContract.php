<?php

namespace SaQle\Core\Support;

use ReflectionClass;
use SaQle\Auth\Exceptions\AuthorizationException;
use SaQle\Core\Exceptions\ValidationException;

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

         $reflection = new ReflectionClass($this);

         foreach ($reflection->getProperties() as $property){
             $type = $property->getType();
             $attributes = $property->getAttributes(BindFrom::class);

             if(!$attributes){
                 continue;
             }

             $bind_instance = $attributes[0]->newInstance();
             $property_name = $property->getName();

             $value         = $this->$property_name ?? null;
             $optional      = $type?->allowsNull() ?? false;
             
             /**
              * Validate only non optional properties or
              * optional properties for which values have been provided
              * */
             if(!$optional || ($optional && !is_null($value))){

                 $rules = RuleParser::parse($bind_instance->rules ?? []);

                 $validator = new FieldValidator(rules: $rules, array: false);

                 $result = $validator->validate($property_name, $value);

                 if($result->isvalid){
                     $this->validated_data[$property_name] = $value;
                 }else{
                     $errors[$property_name] = $result->errors;
                 }
             }else{
                 $this->validated_data[$property_name] = $value;
             }
         }

         if(!empty($errors)){
             throw new ValidationException(context: [
                 'errors' => $errors
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
}