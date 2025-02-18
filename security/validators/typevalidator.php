<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The type validator checks that the type of the data is what it ought to be.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;

use SaQle\Security\Utils\{ScalarValidator, FileValidator};

class TypeValidator extends ValidatorDecorator{
	 use ScalarValidator, FileValidator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
	 }
	 public function validate($input, $config, $code, $message = null){
	 	 $feedback = $this->execute_next_validator($input, $config, $code, $message);
		 match($config['general_type']){
		 	'number' => $this->validate_scalar($feedback, [$this, 'number_type_valid']),
		 	'text'   => $this->validate_scalar($feedback, [$this, 'text_type_valid']),
		 	'upload' => $this->validate_file($feedback, [$this, 'file_type_valid'], 'type')
		 };
		 return $feedback;
	 }

	 public function text_type_valid($input, $config){
		 return [
		     'is_valid' => settype($input, "string") ? true : false, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' is not a text.'
		 ]; 
	 }

     private function set_number_type($number, $type){
		 if(!is_numeric($number)) return false;
		 return settype($number, $type == "float" || $type == "double" ? "float" : "integer") ? true : false;
	 }

	 public function number_type_valid($input, $config){
		 return [
		     'is_valid' => $this->set_number_type($input, $config['primitive_type']), 
			 'error' => $this->get_readable_field_name($config['field_name']). ' is not a number.'
		 ]; 
	 }

     private function is_type_accepted($type, $accepts){
     	$accpeted = false;
     	$typea    = explode("/", $type);
     	for($a = 0; $a < count($accepts); $a++){
     		if($type == $accepts[$a]){
     			$accpeted = true;
     			break;
     		}

     		$ta    = explode("/", $accepts[$a]);
     		if($ta[0] == $typea[0]){
     			$accpeted = true;
     			break;
     		}
     	}
     	return $accpeted;
     }

	 public function file_type_valid($input, $config){
		 return [
		     'is_valid' => $this->is_type_accepted($input, $config['accept']), 
			 'error' => $this->get_readable_field_name($config['field_name']). " file is not of accepted type. Accpeted types are ".implode(",", $config['accept'])
		 ]; 
	 }
}
?>