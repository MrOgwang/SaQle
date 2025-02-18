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
 * The strict validator checks to see if a number contains characters or if a string contains numbers and returns true or false depending on whether that is desired or not. This is for numbers and text only.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;

use SaQle\Security\Utils\ScalarValidator;

class StrictValidator extends ValidatorDecorator{
	 use ScalarValidator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
	 }

	 public function validate($input, $config, $code, $message = null){
	 	 $feedback = $this->execute_next_validator($input, $config, $code, $message);
		 match($config['general_type']){
		 	'number' => $this->validate_scalar($feedback, [$this, 'number_strict_valid']),
		 	'text'   => $this->validate_scalar($feedback, [$this, 'text_strict_valid']),
		 };
		 return $feedback;
	 }

	 public function text_strict_valid($input, $config){
		 return [
		     'is_valid' => (bool)$config['strict'] === true && strpbrk($input, '1234567890') ? false : true, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' cannot contain numbers.'
		 ]; 
	 }

	 public function number_strict_valid($input, $config){
		 return [
		     'is_valid' => (bool)$config['strict'] === true && !is_numeric($input) ? false : true, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' cannot contain letters and other characters.'
		 ]; 
	 }
}
?>