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
 * Checks if a text input has a minimum length or a number input has a minimum value or
 * a file input has a minimum size.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;

use SaQle\Security\Utils\{ScalarValidator, FileValidator};

class MinValidator extends ValidatorDecorator{
	 use ScalarValidator, FileValidator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
	 }
	 public function validate($input, $config, $code, $message = null){
		 $feedback = $this->execute_next_validator($input, $config, $code, $message);
		 match($config['general_type']){
		 	'number' => $this->validate_scalar($feedback, [$this, 'number_min_valid']),
		 	'text'   => $this->validate_scalar($feedback, [$this, 'text_min_valid']),
		 	'upload' => $this->validate_file($feedback, [$this, 'file_min_valid'], 'size')
		 };
		 return $feedback;
	 }

	 public function number_min_valid($input, $config){
	 	 return [
		     'is_valid' => array_key_exists('min_inclusive', $config) && (bool)$config['min_inclusive'] === false ? $input > $config['minimum'] : $input >= $config['minimum'], 
			 'error' => $this->get_readable_field_name($config['field_name']). ' cannot be less than the required minimum of '.$config['minimum']
		 ];
	 }

	 public function text_min_valid($input, $config){
	 	 $input = is_null($input) ? "" : $input;
	 	 return [
		     'is_valid' => array_key_exists('min_inclusive', $config) && (bool)$config['min_inclusive'] === false ? strlen($input) > $config['minimum'] : strlen($input) >= $config['minimum'], 
			 'error' => $this->get_readable_field_name($config['field_name']). ' length cannot be less than the required minimum of '.$config['minimum'].' characters'
		 ];
	 }

	 public function file_min_valid($input, $config){
	 	 $min_bytes = $config['minimum'] * 1024 * 1024;
	 	 return [
		     'is_valid' => array_key_exists('min_inclusive', $config) && (bool)$config['min_inclusive'] === false ? $input > $min_bytes : $input >= $min_bytes, 
			 'error' => $this->get_readable_field_name($config['field_name']).': file size cannot be less than the required minimum of '.$config['minimum']."mbs"
		 ];
	 }
}
?>