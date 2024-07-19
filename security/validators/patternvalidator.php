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
 * Validate against a pattern. Works for both numbers and strings. If the input is a file,
 * the pattern matching is done against incoming file name(s)
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;

use SaQle\Security\Utils\{ScalarValidator, FileValidator};

class PatternValidator extends ValidatorDecorator{
	 use ScalarValidator, FileValidator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
	 }

	 public function validate($input, $config, $code, $message = null){
		 $feedback = $this->execute_next_validator($input, $config, $code, $message);
		 match($config['general_type']){
		 	'number', 'text' => $this->validate_scalar($feedback, [$this, 'is_pattern_valid']),
		 	'upload'         => $this->validate_file($feedback, [$this, 'is_pattern_valid'], 'name')
		 };
		 return $feedback;
	 }

	 private function is_pattern_valid($input, $config){
		 return [
		     'is_valid' => preg_match($config['pattern'], $input), 
			 'error' => $this->get_readable_field_name($config['field_name']). ' does not match the pattern provided.'
		 ];
	 }
}
?>