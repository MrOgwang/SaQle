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
 * Checks if a text input has a maximum length or a number input has a maximum value or
 * a file input has a maximum size.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;

use SaQle\Security\Utils\{ScalarValidator, FileValidator};

class MaxValidator extends ValidatorDecorator{
	 use ScalarValidator, FileValidator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
	 }
	 public function validate($input, $config, $code, $message = null){
		 $feedback = $this->execute_next_validator($input, $config, $code, $message);
		 match($config['general_type']){
		 	'number' => $this->validate_scalar($feedback, [$this, 'number_max_valid']),
		 	'text'   => $this->validate_scalar($feedback, [$this, 'text_max_valid']),
		 	'upload' => $this->validate_file($feedback, [$this, 'file_max_valid'], 'size')
		 };
		 return $feedback;
	 }

	 public function number_max_valid($input, $config){
	 	 return [
		     'is_valid' => array_key_exists('max_inclusive', $config) && (bool)$config['max_inclusive'] === false ? $input < $config['maximum'] : $input <= $config['maximum'], 
			 'error' => $this->get_readable_field_name($config['field_name']). ' must be equal to or less than '.$config['maximum']
		 ];
	 }

	 public function text_max_valid($input, $config){
	 	 $input = is_null($input) ? "" : $input;
	 	 return [
		     'is_valid' => array_key_exists('max_inclusive', $config) && (bool)$config['max_inclusive'] === false ? strlen($input) < $config['maximum'] : strlen($input) <= $config['maximum'], 
			 'error' => $this->get_readable_field_name($config['field_name']). ' length must be equal to or less than '.$config['maximum'].' characters'
		 ];
	 }

	 public function file_max_valid($input, $config){
	 	 $max_bytes = $config['maximum'] * 1024 * 1024;
	 	 return [
		     'is_valid' => array_key_exists('max_inclusive', $config) && (bool)$config['max_inclusive'] === false ? $input < $max_bytes : $input <= $max_bytes, 
			 'error' => $this->get_readable_field_name($config['field_name']).': file size cannot exceed the required maximum of '.$config['maximum']."mbs"
		 ];
	 }
}
?>