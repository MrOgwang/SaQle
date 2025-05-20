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
 * Base validator for all the validator decorators
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Security\Validators;
class InputValidator extends IValidator{
	 public function validate($input, $config, $code, $message = null){
		 return [
		 	"input"   => $input, 
		 	"config"  => $config, 
		 	"code"    => $code, 
		 	"message" => $message
		 ];
	 }
}
