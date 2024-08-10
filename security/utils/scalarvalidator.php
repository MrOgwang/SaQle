<?php
namespace SaQle\Security\Utils;
trait ScalarValidator{
	 public function validate_scalar(&$fb, $callback){
         if(!isset($fb["bypass"])){
         	 if(is_array($fb["input"])){
				 if($fb["config"]["compact"]){
					 $all_clean = [];
				     $bad_indexes = [];
					 $error_messages = [];
					 for($n = 0; $n < count($fb["input"]); $n++){
						 $reg = $callback($fb["input"][$n], $fb["config"]);
						 if($reg['is_valid']){
							 array_push($all_clean, true);
						 }else{
							 array_push($bad_indexes, $n);
							 $error_messages[] = $reg['error'];
						 }
				     }
					 foreach($bad_indexes as $index => $val){
						 unset($fb["input"][$val]);
						 array_values($fb["input"]);
					 }
					 $fb["code"] = count($all_clean) > 0 ? $fb["code"] : 2;
					 $fb["input"] = count($all_clean) > 0 ? $fb["input"] : null;
					 $fb["message"] = implode(", ", $error_messages);
				 }else{
					 for($n = 0; $n < count($fb["input"]); $n++){
						 $reg = $callback($fb["input"][$n], $fb["config"]);
						 if(!$reg['is_valid']){
							 $fb["code"] = 2;
							 $fb["input"] = null;
							 $fb["message"] = $reg['error'];
						     break;
						 }
				     }
				 }
			 }else{
				 $reg = $callback($fb["input"], $fb["config"]);
				 if(!$reg['is_valid']){
					 $fb["code"] = 2;
					 $fb["input"] = null;
					 $fb["message"] = $reg['error'];
				 }
			 }
         }
	 }
}
?>