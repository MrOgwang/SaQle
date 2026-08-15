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

	 	 /**
	 	  * Either there is a 'field' property or a 'bind'
	 	  * property
	 	  * */
	 	 if(array_key_exists("field", $__props) && $__props['field'] instanceof FormFieldContainer){
	 	 	 return Message::ok();
	 	 }

	 	 if(array_key_exists("bind", $__props)){

	 	 	 $field = $this->construct_field($__props['bind']);

	 	 	 if(!$field){
	 	 	 	throw new RuntimeException("The field [".$__props['bind']."] does not exist!");
	 	 	 }

	 	 	 /**
	 	 	  * the __props array may have values that can override 
	 	 	  * the default field attributes.
	 	 	  * */
	 	 	 $default_attrs = $field->get_attributes();

	 	 	 foreach($default_attrs as $attr => $attr_val){
	 	 	 	 if(array_key_exists($attr, $__props)){
	 	 	 	 	 $field->$attr($__props[$attr]);
	 	 	 	 }
	 	 	 }

	 	 	 $__props['field'] = $field;

	 	 }
	 	
		 return Message::ok();
	 } 

}