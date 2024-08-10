<?php
namespace SaQle\Security\Utils;

use SaQle\Commons\Commons;

trait FileValidator{
	 use Commons;
	 public function validate_file(&$fb, $callback, $what){
	 	 if(!isset($fb["bypass"])){
		 	if(is_array($fb["input"][$what])){
				 if($fb["config"]["compact"]){
					 $all_clean      = [];
				     $bad_indexes    = [];
					 $error_messages = [];
					 for($n = 0; $n < count($fb["input"][$what]); $n++){
						 $reg = $callback($fb["input"][$what][$n], $fb["config"]);
						 if($reg['is_valid']){
							 array_push($all_clean, true);
						 }else{
							 array_push($bad_indexes, $n);
							 $error_messages[] = $reg['error'];
						 }
				     }
				     $whats = ['name', 'type', 'size', 'error', 'tmp_name'];
					 foreach($bad_indexes as $index => $val){
					 	 foreach($whats as $w){
					 	 	 unset($fb["input"][$w][$val]);
					 	 	 array_values($fb["input"][$w]);
					 	 }
					 }
					 $fb["code"]    = count($all_clean) > 0 ? $fb["code"] : 2;
					 $fb["input"]   = count($all_clean) > 0 ? $fb["input"] : null;
					 $fb["message"] = implode(", ", $error_messages);
				 }else{
					 for($n = 0; $n < count($fb["input"][$what]); $n++){
						 $reg = $callback($fb["input"][$what][$n], $fb["config"]);
					     if(!$reg['is_valid']){
							 $fb["code"]    = 2;
							 $fb["input"]   = null;
							 $fb["message"] = $reg['error'];
						     break;
					     }
				     }
				 }
			 }else{
				 $reg = $callback($fb["input"][$what], $fb["config"]);
				 if(!$reg['is_valid']){
					 $fb["code"]    = 2;
					 $fb["input"]   = null;
					 $fb["message"] = $reg['error'];
				 }
			 }
		 }
	 }
}
?>