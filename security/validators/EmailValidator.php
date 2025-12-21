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
 * Checks if an input is a valid email address
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;

use SaQle\Security\Utils\ScalarValidator;

class EmailValidator extends ValidatorDecorator{
	 use ScalarValidator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
	 }
	 public function validate($input, $config, $code, $message = null){
		 $feedback = $this->execute_next_validator($input, $config, $code, $message);
	 	 $this->validate_scalar($feedback, [$this, 'is_email_valid']);
		 return $feedback;
	 }
	 public function is_email_valid($input, $config){
	 	 $is_valid = filter_var($input, FILTER_VALIDATE_EMAIL);
		 if($is_valid){
			 $invalid_characters = array(";", ":", ",", "\n", "\r");
		     foreach($invalid_characters as $c) $input = str_replace($c, "", $input);
		 }
		 return [
		     'is_valid' => $is_valid, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' cannot be less than zero.'
		 ];
	 }
}
