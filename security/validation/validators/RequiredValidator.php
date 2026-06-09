<?php

namespace SaQle\Security\Validation\Validators;

use SaQle\Security\Validation\Abstracts\IValidator;
use SaQle\Security\Validation\Types\{
     ValidationResult,
     ValidationAction
};

class RequiredValidator extends IValidator {

     protected function threshold_type(): string {
         return 'bool';
     }

     public function validate(mixed $value, array $context = []): ValidationResult {
         /**
          * If the value has been provided, it doesnt matter whether it is 
          * required or optional. Pass the validation and continue
          * validation chain.
          * */
         if(!is_null($value)){
             return new ValidationResult(
                 isvalid: true, 
                 message: null, 
                 normalized: null, 
                 action: ValidationAction::CONTINUE
             );
         }


         /**
          * If required and value is missing, fail
          * and stop the validation chain immediatly
          * */
         if($this->threshold === true && is_null($value)){
             return new ValidationResult(
                 isvalid: false, 
                 message: "{$this->field} is required.", 
                 normalized: null, 
                 action: ValidationAction::STOP
             );
         }

         /**
          * If optional and value is missing, the validation passes
          * but stop the validation chain immediatly
          * */
         if($this->threshold === false && is_null($value)){
             return new ValidationResult(
                 isvalid: true, 
                 message: null, 
                 normalized: null, 
                 action: ValidationAction::STOP
             );
         }
     }
}
