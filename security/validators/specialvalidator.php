<?php
namespace SaQle\Security\Validators;
class SpecialValidator extends ValidatorDecorator{
	 private $scalar_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->scalar_validator = new ScalarValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 $this->scalar_validator->validate($fb, [$this, 'is_valid']);
		 return $fb;
	 }
	 public function is_valid(&$input, $config){
		 switch($config["type"]){
			 case "email":
				return $this->validate_email($input, $config);
			 break;
			 case "phone":
				 return $this->validate_phone($input, $config);
			 break;
			 case "url":
			     return $this->validate_url($input, $config);
			 break;
			 case "time":
			     return $this->validate_time($input, $config);
			 break;
			 case "date":
			 case "datetime":
			     return $this->validate_date($input, $config);
			 break;
			 default:
				 return [
					 'is_valid' => true, 
					 'error' => ''
				 ]; 
			 break;
		 }
	 }
	 private function validate_email(&$email, $config){
		 $is_valid = filter_var($email, FILTER_VALIDATE_EMAIL);
		 if($is_valid){
			 $invalid_characters = array(";", ":", ",", "\n", "\r");
		     foreach($invalid_characters as $c) $email = str_replace($c, "", $email);
		 }
		 return [
		     'is_valid' => $is_valid, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' is not a valid email address.'
		 ]; 
	 }
	 private function validate_phone($phone, $config){
		 return [
		     'is_valid' => true, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' is not a valid phone number.'
		 ]; 
	 }
	 private function validate_url($phone, $config){
		 return [
		     'is_valid' => true, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' is not a valid url.'
		 ]; 
	 }
	 private function validate_time($phone, $config){
		 return [
		     'is_valid' => true, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' is not a valid time.'
		 ]; 
	 }
	 private function validate_date($phone, $config){
		 return [
		     'is_valid' => true, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' is not a valid date.'
		 ];  
	 }
}
?>