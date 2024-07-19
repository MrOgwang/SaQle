<?php
namespace SaQle\Security\Models;

class ModelValidator{
	public static function validate(array $expected, $data){
		$dirty  = [];
		$clean  = [];
		$status = 0;
		foreach($expected as $key => $config){
			$feedback = FieldValidator::validate(field: $key, config: $config, value: $data[$key] ?? null);
			if($feedback['code'] == 0){
				 $clean[$key] = $feedback['input'];
			}else{
				 $status = 1;
				 $dirty[$key] = $feedback["message"];
			}
		}
		return ['status' => $status, 'clean' => $clean, 'dirty' => $dirty];
	}
}
?>

