<?php
declare(strict_types=1);

namespace SaQle\Core\Support;

use SaQle\Http\Request\Request;
use SaQle\Core\Exceptions\Http\UnauthorizedException;

abstract class RequestContract {
     //the current request object: carries input
     protected Request $request;

     //validated and normalized data
     protected array $validated = [];

     final public function __construct(Request $request){
         $this->request = $request;
         $this->authorize_request();
         $this->validate_input();
         $this->hydrate();
     }

     //authorization gate
     abstract protected function authorize(): bool;

     //validation rules mapped to declared properties
     abstract protected function rules(): array;

     //sources from which to bind incoming input
     protected function sources(): array {
         //override if needed
     }

     //optional input normalization hook
     protected function prepare(): void {
         //override if needed
     }

     //Access validated data safely
     final public function validated(): array {
        return $this->validated;
     }

     //internal helpers

     final protected function authorize_request(): void {
         if(!$this->authorize()){
             throw new UnauthorizedException();
         }
     }

     final protected function validate_input(): void {
         $this->prepare();

         $rules = $this->rules();
         $errors = Validator::validate($this->input, $rules);

         if($errors){
             throw new ValidationException($errors);
         }

         $this->validated = Validator::filtered($this->input, $rules);
     }

     //Hydrate typed properties
     final protected function hydrate(): void {
         foreach ($this->validated as $key => $value){
             if(property_exists($this, $key)){
                 $this->{$key} = $value;
             }
         }
     }
}
