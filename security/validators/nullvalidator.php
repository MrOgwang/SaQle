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
 * Validate null values. A value is null if the key provided is null, or if the key is not
 * provided at all in the data array.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;

use SaQle\Security\Utils\{ScalarValidator, FileValidator};

class NullValidator extends ValidatorDecorator{
	 use ScalarValidator, FileValidator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
	 }

	 public function validate($input, $config, $code, $message = null){
	 	 $feedback = $this->execute_next_validator($input, $config, $code, $message);
	 	 $is_valid = match($config['general_type']){
		 	'text', 'number' => $this->validate_scalar($feedback, [$this, 'null_valid']),
		 	'upload' => true
		 };
		 if($is_valid && is_null($input)){
		 	 $feedback['bypass'] = true;
		 }
		 return $feedback;
	 }

	 public function null_valid($input, $config){
	 	 return [
		     'is_valid' => (bool)$config['allow_null'] === false && is_null($input) ? false : true, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' cannot be null'
		 ];
	 }
}
?>