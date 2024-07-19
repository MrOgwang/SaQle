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
abstract class IValidator{
}
class InputValidator extends IValidator{
	 public function validate($input, $config, $code, $message = null){
		 return array("input"=>$input, "config"=>$config, "code"=>$code, "message"=>$message);
	 }
}
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
class NullValidator extends ValidatorDecorator{
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 $fb["config"]['bypass'] = is_null($fb["input"]) ? true : false;
		 if(isset($fb["config"]["allow_null"])){
			 if(!$fb["config"]["allow_null"] && is_null($fb["input"])){
				 $fb["code"] = 1;
				 $fb["message"] = $this->get_readable_field_name($fb["config"]["field_name"]). " cannot be null.";
			 }
		 }
		 return $fb;
	 }
}
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
class TypeValidator extends ValidatorDecorator{
	 private $file_validator;
	 private $scalar_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->file_validator = new FileValidator();
		 $this->scalar_validator = new ScalarValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 switch($fb["config"]['general_type']){
			 case "number":
			 case "text":
				 $this->scalar_validator->validate($fb, [$this, 'is_type_valid']);
			 break;
			 case "upload":
				 $this->file_validator->validate($fb, "type");
			 break;
		 }
		 return $fb;
	 }
	 private function set_number_type($number, $type){
		 if(!is_numeric($number)) return false;
		 return settype($number, $type == "float" || $type == "double" ? "float" : "integer") ? true : false;
	 }
	 public function is_type_valid($input, $config){
		 if($config['general_type'] === "number"){
			 $is_valid = $this->set_number_type($input, $config['type']);
			 $error = $this->get_readable_field_name($config['field_name']). ' is not a number';
		 }else{
			 $is_valid = settype($input, "string") ? true : false;
			 $error = $this->get_readable_field_name($config['field_name']). ' is not a text.';
		 }
		 return [
		     'is_valid' => $is_valid, 
			 'error' => $error
		 ];
	 }
}
class PatternValidator extends ValidatorDecorator{
	 private $scalar_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->scalar_validator = new ScalarValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 if(isset($fb['config']['pattern'])) $this->scalar_validator->validate($fb, [$this, 'is_pattern_valid']);
		 return $fb;
	 }
	 private function is_pattern_valid($input, $config){
		 return [
		     'is_valid' => preg_match($config['pattern'], $input), 
			 'error' => $this->get_readable_field_name($config['field_name']). ' does not match the pattern provided.'
		 ];
	 }
}
class ZeroValidator extends ValidatorDecorator{
	 private $scalar_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->scalar_validator = new ScalarValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 if(isset($fb["config"]['allow_zero'])) $this->scalar_validator->validate($fb, [$this, 'is_zero_valid']);
		 return $fb;
	 }
	 public function is_zero_valid($input, $config){
		 return [
		     'is_valid' => $config['allow_zero'] || (!$config['allow_zero'] && (int)$input !== 0) ? true : false, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' cannot be a zero.'
		 ];
	 }
}
class MaxValidator extends ValidatorDecorator{
	 private $file_validator;
	 private $scalar_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->file_validator = new FileValidator();
		 $this->scalar_validator = new ScalarValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 if(isset($fb["config"]['max'])){
			 if(in_array($fb["config"]['general_type'], array("number", "text"))){
				 $this->scalar_validator->validate($fb, [$this, 'is_max_valid']);
			 }else{
				 $this->file_validator->validate($fb, "max");
			 }
		 }
		 return $fb;
	 }
	 public function is_max_valid($input, $config){
		 if($config['general_type'] === "number"){
			 $is_valid = $config['max_inclusive'] ? $input <= $config['max'] : $input < $config['max'];
			 $error = $this->get_readable_field_name($config['field_name']). " cannot be more than the required maximum of ". $config['max'];
		 }else{
			 $is_valid = $config['max_inclusive'] ? strlen($input) <= $config['max'] : strlen($input) < $config['max'];
			 $error = $this->get_readable_field_name($config['field_name']). ": length cannot be more than the required maximum of ". $config['max'];
		 }
		 return [
		     'is_valid' => $is_valid, 
			 'error' => $error
		 ]; 
	 }
}
class MinValidator extends ValidatorDecorator{
	 private $file_validator;
	 private $scalar_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->file_validator = new FileValidator();
		 $this->scalar_validator = new ScalarValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 if(isset($fb["config"]['min'])){
			 if(in_array($fb["config"]['general_type'], array("number", "text"))){
				 $this->scalar_validator->validate($fb, [$this, 'is_min_valid']);
			 }else{
				 $this->file_validator->validate($fb, "min");
			 }
		 }
		 return $fb;
	 }
	 public function is_min_valid($input, $config){
		 if($config['general_type'] === "number"){
			 $is_valid = $config['min_inclusive'] ? $input >= $config['min'] : $input > $config['min'];
			 $error = $this->get_readable_field_name($config['field_name']). " cannot be less than the required minimum of ". $config['min'];
		 }else{
			 $is_valid = $config['min_inclusive'] ? strlen($input) >= $config['min'] : strlen($input) > $config['min'];
			 $error = $this->get_readable_field_name($config['field_name']). ": length cannot be less than the required minimum of ". $config['min'];
		 }
		 return [
		     'is_valid' => $is_valid, 
			 'error' => $error
		 ]; 
	 }
}
class StrictValidator extends ValidatorDecorator{
	 private $scalar_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->scalar_validator = new ScalarValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 if(isset($fb['config']['strict'])) $this->scalar_validator->validate($fb, [$this, 'is_strict_valid']);
		 return $fb;
	 }
	 public function is_strict_valid($input, $config){
		 return [
		     'is_valid' => !$config['strict'] || ($config['strict'] && !strpbrk($input, '1234567890')) ? true : false, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' cannot contain numbers.'
		 ]; 
	 }
}
class EmptyValidator extends ValidatorDecorator{
	 private $scalar_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->scalar_validator = new ScalarValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 if(isset($fb['config']['allow_empty'])) $this->scalar_validator->validate($fb, [$this, 'is_empty_valid']);
		 return $fb;
	 }
	 public function is_empty_valid($input, $config){
		 return [
		     'is_valid' => $config['allow_empty'] || (!$config['allow_empty'] && $input) ? true : false, 
			 'error' => $this->get_readable_field_name($config['field_name']). ' cannot be an empty string.'
		 ]; 
	 }
}
class LengthValidator extends ValidatorDecorator{
	 private $scalar_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->scalar_validator = new ScalarValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 if(isset($fb['config']['len'])) $this->scalar_validator->validate($fb, [$this, 'is_length_valid']);
		 return $fb;
	 }
	 public function is_length_valid($input, $config){
		 return [
		     'is_valid' => strlen($input) === $config['len'], 
			 'error' => $this->get_readable_field_name($config['field_name']). ' has a length exceeding the required length of '.$config['len']
		 ]; 
	 }
}
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
class ChoicesValidator extends ValidatorDecorator{
	 private $scalar_validator;
	 public function __construct(IValidator $validator){
		 parent::__construct($validator);
		 $this->scalar_validator = new ScalarValidator();
	 }
	 public function validate($input, $config, $code, $message = null){
		 $this->fill_defaults($config);
		 $fb = $this->execute_next_validator($input, $config, $code, $message);
		 if($fb["config"]['bypass']) return $fb;
		 if(isset($fb["config"]['choices'])) $this->scalar_validator->validate($fb, [$this, 'is_choice_valid']);
		 return $fb;
	 }
	 public function is_choice_valid($input, $config){
		 return [
		     'is_valid' => in_array($input, $config['choices']), 
			 'error' => $this->get_readable_field_name($config['field_name']). ' is not among the options allowed: options include['.implode(', ', $config['choices']).']'
		 ];
	 }
}
?>