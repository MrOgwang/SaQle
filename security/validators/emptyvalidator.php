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
 * Checks if a text input is empty
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;

use SaQle\Security\Utils\ScalarValidator;

class EmptyValidator extends ValidatorDecorator{
	 use ScalarValidator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
	 }
	 public function validate($input, $config, $code, $message = null){
		 $feedback = $this->execute_next_validator($input, $config, $code, $message);
	 	 $is_valid = match($config['general_type']){
		 	'text', 'number' => $this->validate_scalar($feedback, [$this, 'is_empty_valid']),
		 	'upload' => true
		 };
		 if($is_valid && $config['general_type'] !== 'upload' && trim($input) == ""){
		 	 $feedback['bypass'] = true;
		 }
		 return $feedback;
	 }
	 public function is_empty_valid($input, $config){
		 return [
		     'is_valid' => (bool)$config['allow_empty'] === false && trim($input) == "" ? false : true, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' cannot be an empty string.'
		 ]; 
	 }
}
?>