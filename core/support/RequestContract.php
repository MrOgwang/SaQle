<?php

namespace SaQle\Core\Support;

use ReflectionClass;
use SaQle\Core\Exceptions\Http\UnauthorizedException;
use SaQle\Core\Exceptions\Model\ValidationException;

abstract class RequestContract {

     protected array $validated_data = [];

     final public function validate_and_authorize(): void {
         if(!$this->authorize()) {
             throw new UnauthorizedException('This action is unauthorized.');
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
             $attributes = $property->getAttributes(BindFrom::class);

             if(!$attributes){
                 continue;
             }

             $bind_instance = $attributes[0]->newInstance();
             $property_name = $property->getName();
             $value         = $this->$property_name ?? null;
             $rules         = RuleParser::parse($bind_instance->rules ?? []);

             $validator = new FieldValidator(
                 rules: $rules,
                 array: false
             );

             $result = $validator->validate($property_name, $value);

             if($result->isvalid){
                 $this->validated_data[$property_name] = $value;
             }else{
                 $errors[$property_name] = $result->errors;
             }
         }

         if(!empty($errors)){
             throw new ValidationException([
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