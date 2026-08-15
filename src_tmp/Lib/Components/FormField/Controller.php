<?php

namespace SaQle\Lib\Components\FormField;

use SaQle\Http\Response\Message;
use SaQle\Core\Registries\ModelRegistry;
use SaQle\Core\Ui\Forms\{
	 FormField as FormFieldContainer,
	 FormFieldsCompiler
};
use RuntimeException;

class Controller {

	 private function construct_field(string $field_key){

	 	 $field_key_parts = explode(":", $field_key);

	 	 $model_key = $field_key_parts[0];

	 	 $field_name = $field_key_parts[1];

	 	 $model_class = ModelRegistry::get_model_class($model_key);

	 	 $form_fields = FormFieldsCompiler::compile($model_class, true);

	 	 return $form_fields[$field_name] ?? null;
        
	 }
	 
	 public function get(array &$__props) : Message {

	 	 if(array_key_exists("field", $__props) && !$__props['field'] instanceof FormFieldContainer){

	 	 	 $field = $this->construct_field($__props['field']);

	 	 	 if(!$field){
	 	 	 	throw new RuntimeException("The field [".$__props['field']."] does not exist!");
	 	 	 }

	 	 	 $__props['field'] = $field;

	 	 }
	 	
		 return Message::ok();
	 } 

}