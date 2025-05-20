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
 * the pattern matching done against incoming file name(s)
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;
class UploadValidator extends ValidatorDecorator{
	 private $file_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->file_validator = new FileValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 $this->file_validator->validate($fb, "upload");
		 return $fb;
	 }
}
