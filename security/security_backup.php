<?php
namespace SaQle\Security;

use SaQle\Commons as Commons;

class Security{
	 use Commons\Commons;
	 protected $clean = array();
	 protected $dirty = array();
	 protected $status = 0;
	 private function get_incoming($key, $origin, $method = null, $src = null){
		 $origin_methods = array("form" => "post", "url" => "get", "upload" => "file", "param" => "self");
		 $method_containers = array("post" => $_POST, "get" => $_GET, "file" => $_FILES, "self" => $src);
		 $method = !is_null($method) ? $method : $origin_methods[$origin];
		 if(!isset($method_containers[$method][$key])){
			 $method_containers[$method][$key] = null;
		 }
		 $incoming = $method_containers[$method][$key];
		 return $incoming;
	 }
	 private function get_input_error_message($level, $input_key){
		 $input_key_name = ucwords(str_replace("_", " ", $input_key));
		 $default_messages = array("1" => "Empty ".$input_key_name, "2" => "Invalid ".$input_key_name);
		 return $default_messages[$level];
	 }
	 public function fill_defaults(&$config){
		 $config['compact'] = isset($config['compact']) ? $config['compact'] : true;
		 $config['method'] = isset($config['method']) ? $config['method'] : null;
		 $config['allow_null'] = isset($config['allow_null']) ? $config['allow_null'] : false;
		 switch($config['type']){
			 case "tinyint":
			 case "smallint":
			 case "int":
			 case "float":
			 case "mediumint":
			 case "bigint":
			 case "float":
			 case "double":
			 case "number":
				 $config['allow_zero'] = isset($config['allow_zero']) ? $config['allow_zero'] : true;
				 $config['max_inclusive'] = isset($config['max_inclusive']) ? $config['max_inclusive'] : false;
				 $config['min_inclusive'] = isset($config['min_inclusive']) ? $config['min_inclusive'] : false;
				 $config['general_type'] = "number";
			 break;
			 case "char":
			 case "varchar":
			 case "tinytext":
			 case "text":
			 case "mediumtext":
			 case "longtext":
			 case "string":
			 case "email":
			 case "phone":
			 case "url":
			 case "date":
			 case "time":
			 case "datetime":
			     $config['strict'] = isset($config['strict']) ? $config['strict'] : false;
				 $config['allow_empty'] = isset($config['allow_empty']) ? $config['allow_empty'] : true;
				 $config['max_inclusive'] = isset($config['max_inclusive']) ? $config['max_inclusive'] : false;
				 $config['min_inclusive'] = isset($config['min_inclusive']) ? $config['min_inclusive'] : false;
				 $config['general_type'] = "text";
			 break;
			 case "file":
				 $config['max'] = isset($config['max']) ? $config['max'] : MAX_FILE_SIZE;
				 $config['accept'] = isset($config['accept']) ? $config['accept'] : array_merge(array_keys(self::$image_mime_types), array_keys(self::$video_mime_types), array_keys(self::$audio_mime_types), array_keys(self::$text_mime_types), array_keys(self::$document_mime_types), array_keys(self::$application_mime_types));
			     $config['max_inclusive'] = isset($config['max_inclusive']) ? $config['max_inclusive'] : true;
				 $config['min_inclusive'] = isset($config['min_inclusive']) ? $config['min_inclusive'] : false;
				 $config['general_type'] = "upload";
			 break;
		 }
		 $config['defaults_ready'] = true;
	 }
	 /*
	 Extract input incoming from user through forms via post and files, and through urls via get
	 @param ARRAY $expected: an associative array of keys representating the names of inputs to expect and values representating
							 instructions to be used to determine whether an input is clean or dirty.
			Instructions include:
         1. type: type of input expected: int, float, number, string, email, phone, url, time, date, datetime
		 2. compact: true/false. Used with array input values. If true, the input value will be considered clean if and only if 
		             all of its individual values are clean. if false, the input value will be considered clean if any one of its
					 individual values are clean.
		 3. method: include post, get, file or data access object to indicate where these input is coming from
		 4. pattern: provide a regex patter if you want your value to be tested against a pattern. If a pattern is provided, it takes precedent
		             over all the other instructions.
		 5. allow_null: True/False: if the value of expected input isnt provided, should it be considered clean or dirty. Defaults to false.
		     Number related instructions
		 5. allow_zero: Can be used with types int, float or number. True/False. If true, zero values are considered clean, otherwise dirty.
		 6. max: Can be used with types int, float or number: The maximum value beyond which our input value is dirty.
		 7. min: Can be used with types int, float or number: The minimum value below which our input value is dirty.
		 8. max_inclusive: Can be used alongside max. Defaults to false. 
		 9. min_inclusive: Can be used alongside min. Defaults to false.
		     Text related instructions
		 10. strict: True/False. True to indicate that text should only contain letters, otherwise false.
		 11. allow_empty: True/False. True to indicate that empty string is clean, otherwise dirty.
		 12. len: Integer representating the length of text.Overrides max and min.
		 13. max: The maximum length of text beyond which our input value is dirty.
		 14. min: The minimum length of text below which our input value is dirty.
		 15. max_inclusive: Can be used alongside max. Defaults to false. 
		 16. min_inclusive: Can be used alongside min. Defaults to false.
	         File related instructions
		 17: accept: an array of file types to accept.
		 
		 18.choices: an array of values against which the incoming value will be compared
		 19.required: true of false to indicate whether this value is required or not.
		 20.error_messages: an associative array with error messages to be used by all the validators.
         		 
	 @param optional STRING $origin: a string representating source of input values, include form, url, upload or param. Defaults to form if not provided.
	 @param optional COMPLEX $src: if the origin of input values is param, this is the object containing those input values.
	 @return ARRAY: an associative array with three keys:
		status: an INTEGER value indicating whether all values are clean or one or more are dirty. 0 for all clean, 1 for not all clean.
		clean: an associative ARRAY containing clean values.
		dirty: an associative ARRAY containing dirty values and error messages.
	 */
	 public function extract_input(array $expected, $origin = "form", $src = null){
		 foreach($expected as $key => $config){
			 $config['field_name'] = $key;
			 $this->fill_defaults($config);
			 $incoming = $this->get_incoming($key, $origin, $config['method'], $src);
			 print_r($incoming);
			 switch($config['general_type']){
				 case "number":
				     $validator = new TypeValidator(
									new MinValidator(
										new MaxValidator(
											new ZeroValidator(
												new PatternValidator(
													new ChoicesValidator(
														new NullValidator(
															new InputValidator())))))));
				 break;
				 case "text":
				     $validator = new ChoicesValidator(
									new SpecialValidator(
										new MinValidator(
											new MaxValidator(
												new LengthValidator(
													new StrictValidator(
														new EmptyValidator(
															new PatternValidator(
																new TypeValidator(
																	new NullValidator(
																		new InputValidator()))))))))));
				 break;
				 case "upload":
				     $validator = new MinValidator(
									new MaxValidator(
										new TypeValidator(
											new UploadValidator(
												new NullValidator(
													new InputValidator())))));
				 break;
			 }
			 $validation_feedback = $validator->validate($incoming, $config, 0);
			 if($validation_feedback["code"] === 0){
				 $this->clean[$key] = $validation_feedback["input"];
			 }else{
				 $this->status = 1;
				 //$this->dirty[$key] = $this->get_input_error_message($validation_feedback["code"], $key);
				 $this->dirty[$key] = $validation_feedback["message"];
			 }
         }
		 return array("status"=>$this->status, "feedback"=>array("clean"=>$this->clean, "dirty"=>$this->dirty));
	 }
}
class FileValidator{
	 use Commons\Commons;
	 private function is_null_valid($error_code, $config){
		 $allow_null_or_empty = ((isset($config['allow_empty']) && $config['allow_empty'] === true) || (isset($config['allow_null']) && $config['allow_null'] === true)) ? true : false;
		 return $error_code === UPLOAD_ERR_NO_FILE && $allow_null_or_empty ? true : false;
	 }
	 private function validate_upload($upload_error, $error_code, $config, $file_name){
		 $file_name = self::get_shortened_string($file_name, 30, true);
		 switch($error_code){
			 case UPLOAD_ERR_OK:
				 $error = "{$file_name}: file uploaded successfully.";
			 break;
			 case UPLOAD_ERR_INI_SIZE:
			 case UPLOAD_ERR_FORM_SIZE:
			     $error = "{$file_name}: file size exceeds maximum.";
			 break;
			 case UPLOAD_ERR_PARTIAL:
			     $error = "{$file_name}: file was not uploaded to completion.";
			 break;
			 case UPLOAD_ERR_NO_FILE:
			     $error = "{$file_name}: no file was uploaded.";
			 break;
			 case UPLOAD_ERR_NO_TMP_DIR:
			     $error = "{$file_name}: missing a temporary folder to save file.";
			 break;
			 case UPLOAD_ERR_CANT_WRITE:
			     $error = "{$file_name}: could not write file to disk.";
			 break;
			 case UPLOAD_ERR_EXTENSION:
			     $error = "{$file_name}: a php extension stopped the file from uploading.";
			 break;
		 }
		 return [
		     'is_valid' => $error_code === UPLOAD_ERR_OK ? true : $this->is_null_valid($upload_error, $config),
			 'error' => $error
		 ];
	 }
	 private function validate_type($error_code, $file_type, $config, $file_name){
		 $file_name = self::get_shortened_string($file_name, 30, true);
		 return [
		     'is_valid' => $this->is_null_valid($error_code, $config) ? true : in_array($file_type, $config['accept']),
			 'error' => "{$file_name}: file is not of accepted type. Accpeted types are ".implode(",", $config['accept'])
		 ];
	 }
	 private function validate_min($error_code, $min, $config, $file_name){
		 $file_name = self::get_shortened_string($file_name, 30, true);
		 $min_kbs = $config['min'] * 1024 * 1024;
		 return [
		     'is_valid' => $this->is_null_valid($error_code, $config) ? true : ($config['min_inclusive'] ? $min >= $min_kbs : $min > $min_kbs),
			 'error' => "{$file_name}: file size cannot be less than the required minimum of " .$config['min']."mbs"
		 ];
	 }
	 private function validate_max($error_code, $max, $config, $file_name){
		 $file_name = self::get_shortened_string($file_name, 30, true);
		 $max_kbs = $config['max'] * 1024 * 1024;
		 return [
		     'is_valid' => $this->is_null_valid($error_code, $config) ? true : ($config['max_inclusive'] ? $max <= $max_kbs : $max < $max_kbs),
			 'error' => "{$file_name}: file size cannot exceed the required maximum of ".$config['max']."mbs"
		 ];
	 }
	 private function validate_file($error_code, $input, $what, $config, $file_name){
		 switch($what){
			 case "upload":
				return $this->validate_upload($error_code, $input, $config, $file_name);
			 break;
			 case "type":
			    return $this->validate_type($error_code, $input, $config, $file_name);
			 break;
			 case "min":
				return $this->validate_min($error_code, $input, $config, $file_name);
			 break;
			 case "max":
			     return $this->validate_max($error_code, $input, $config, $file_name);
			 break;
		 }
	 }
	 public function validate(&$fb, $what){
		 $validation_cats = array("upload"=>"error", "type"=>"type", "min"=>"size", "max"=>"size");
		 if(!is_null($fb["input"]) && is_array($fb["input"]['name'])){
			 if($fb["config"]["compact"]){
				 $all_clean = [];
			     $bad_indexes = [];
				 $error_messages = [];
				 for($n = 0; $n < count($fb["input"]['name']); $n++){
					 $reg = $this->validate_file($fb["input"]['error'][$n], $fb["input"][$validation_cats[$what]][$n], $what, $fb["config"], $fb["input"]['name'][$n]);
					 if($reg['is_valid']){
						 array_push($all_clean, true);
					 }else{
						 array_push($bad_indexes, $n);
						 $error_messages[] = $reg['error'];
					 }
			     }
				 foreach($bad_indexes as $index => $val){
					 unset($fb["input"]['type'][$val]);
					 array_values($fb["input"]['type']);
					 unset($fb["input"]['size'][$val]);
					 array_values($fb["input"]['size']);
					 unset($fb["input"]['error'][$val]);
					 array_values($fb["input"]['error']);
					 unset($fb["input"]['tmp_name'][$val]);
					 array_values($fb["input"]['tmp_name']);
					 unset($fb["input"]['name'][$val]);
					 array_values($fb["input"]['name']);
				 }
				 $fb["code"] = count($all_clean) > 0 ? $fb["code"] : 2;
				 $fb["input"] = count($all_clean) > 0 ? $fb["input"] : null;
				 $fb["message"] = implode(", ", $error_messages);
			 }else{
				 for($n = 0; $n < count($fb["input"]['name']); $n++){
					 $reg = $this->validate_file($fb["input"]['error'][$n], $fb["input"][$validation_cats[$what]][$n], $what, $fb["config"], $fb["input"]['name'][$n]);
				     if(!$reg['is_valid']){
						 $fb["code"] = 2;
						 $fb["input"] = null;
						 $fb["message"] = $reg['error'];
					     break;
				     }
			     }
			 }
		 }elseif(!is_null($fb["input"]) && !is_array($fb["input"]['name'])){
			 $reg = $this->validate_file($fb["input"]['error'], $fb["input"][$validation_cats[$what]], $what, $fb["config"], $fb["input"]['name']);
			 if(!$reg['is_valid']){
				 $fb["code"] = 2;
				 $fb["input"] = null;
				 $fb["message"] = $reg['error'];
			 }
		 }
	 }
}
class ScalarValidator{
	 public function validate(&$fb, $callback){
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
?>