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
 * Checks if an input is a valid phone number
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;

use SaQle\Security\Utils\ScalarValidator;

class PhoneValidator extends ValidatorDecorator{
	 use ScalarValidator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
	 }
	 public function validate($input, $config, $code, $message = null){
		 $feedback = $this->execute_next_validator($input, $config, $code, $message);
	 	 $this->validate_scalar($feedback, [$this, 'is_phone_valid']);
		 return $feedback;
	 }
	 public function is_phone_valid($input, $config){
		 return [
		     'is_valid' => true, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' is not a valid phone number.'
		 ];
	 }
}
