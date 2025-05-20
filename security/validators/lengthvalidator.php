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
 * Checks if a text input has a specified number of characters or number input has a specified
 * number of digits.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;

use SaQle\Security\Utils\ScalarValidator;

class LengthValidator extends ValidatorDecorator{
	 use ScalarValidator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
	 }
	 public function validate($input, $config, $code, $message = null){
		 $feedback = $this->execute_next_validator($input, $config, $code, $message);
		 match($config['general_type']){
		 	'number' => $this->validate_scalar($feedback, [$this, 'number_length_valid']),
		 	'text'   => $this->validate_scalar($feedback, [$this, 'text_length_valid']),
		 	default  => true
		 };
		 return $feedback;
	 }

	 public function text_length_valid($input, $config){
	 	 $input = is_null($input) ? "" : $input;
	 	 $strict_length = array_key_exists("strict_length", $config) && (bool)$config['strict_length'] === true;
		 return [
		     'is_valid' => $strict_length ? strlen($input) === $config['length'] : strlen($input) <= $config['length'], 
			 'error' => $this->get_readable_field_name($config['field_name']). ' has a length that is not equal to the required length of '.$config['length']
		 ]; 
	 }

	 public function number_length_valid($input, $config){
	 	 $strict_length = array_key_exists("strict_length", $config) && (bool)$config['strict_length'] === true;
	 	 $count = $input !== 0 ? floor(log10(abs($input ?? 0)) + 1) : 1;
		 return [
		     'is_valid' => $strict_length ? $count === $config['length'] : $count <= $config['length'], 
			 'error' => $this->get_readable_field_name($config['field_name']). ' has a length exceeding the required length of '.$config['length']
		 ]; 
	 }

	 
}
