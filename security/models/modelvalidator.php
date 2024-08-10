<?php
namespace SaQle\Security\Models;

class ModelValidator{
	public static function validate(array $expected, $data){
		$dirty  = [];
		$clean  = [];
		$status = 0;
		$bypassed = [];
		foreach($expected as $key => $config){
			 if($config === false){ //bypass validation for this field.
			 	 $bypassed[] = $key;
			 }else{
			 	 $feedback = FieldValidator::validate(field: $key, config: $config, value: $data[$key] ?? null);
				 if($feedback['code'] == 0){
					 $clean[$key] = $feedback['input'];
				 }else{
					 $status = 1;
					 $dirty[$key] = $feedback["message"];
				 }
			 }
		}
		foreach($bypassed as $bp){
			 if(array_key_exists($bp, $data)){
			 	 $clean[$bp] = $data[$bp];
			 }
		}
		return ['status' => $status, 'clean' => $clean, 'dirty' => $dirty];
	}
}
?>

