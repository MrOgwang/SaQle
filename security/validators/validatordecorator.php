<?php
namespace SaQle\Security\Validators;
abstract class ValidatorDecorator extends IValidator{
	 protected $validator;
	 public function __construct(IValidator $validator = null){
		 $this->validator = $validator;
	 }
	 protected function execute_next_validator($input, $config, $code, $message){
		 return $this->validator ? $this->validator->validate($input, $config, $code, $message) 
		 : ["input"=>$input, "config"=>$config, "code"=>$code, "message"=>$message];
	 }
	 protected function fill_defaults(&$config){
		 if(!array_key_exists("defaults_ready", $config)){
			 (new Security())->fill_defaults($config);
		 }
	 }
	 protected function get_readable_field_name($name){
		 return ucwords(str_replace("_", " ", $name));
	 }
}
?>