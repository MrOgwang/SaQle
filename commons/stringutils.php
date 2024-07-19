<?php
namespace SaQle\Commons;
use stdClass;
trait StringUtils{
	 public static function random_string($max_length = 30, $min_length = 30){
		 $min_length = $max_length < $min_length ? $max_length : $min_length;
         $half_min = ceil($min_length / 2);
         $half_max = ceil($max_length / 2);
         $bytes = random_bytes(rand($half_min, $half_max));
         $random_string = bin2hex($bytes);
         $random_string = strlen($random_string) > $max_length ? substr($random_string, 0, -1) : $random_string;
         return $random_string;
	 }
	 static public function get_shortened_string($string, $final_string_length, $file_style = false){
		 $final_string = $string;
		 $string_length = strlen($string);
		 if($string_length > $final_string_length){
			 if($file_style){
				 $string_array = explode(".", $string);
				 $extension = $string_array[count($string_array) - 1];
				 unset($string_array[count($string_array) - 1]);
				 $string_array = array_values($string_array);
				 $new_string = implode($string_array);
				 $char_array = str_split($new_string);
				 $extension_length = strlen($extension);
				 $ellipsis_length = 4;
				 $default_length = $extension_length + $ellipsis_length;
				 $length_required = $final_string_length - $default_length;
				 $left_char_array = array();
				 $right_char_array = array();
				 $left_index = 0;
				 $right_index = count($char_array) - 1;
				 while($length_required > 0){
					 array_push($left_char_array, $char_array[$left_index]);
					 $length_required -= 1;
					 $left_index += 1;
					 if($length_required > 0){
						 array_push($right_char_array, $char_array[$right_index]);
						 $length_required -= 1;
						 $right_index -= 1;
					 }
				 }
				 $final_string = implode($left_char_array) ."..." .implode($right_char_array) .".".$extension;
			 }else{
				 $final_string = substr($string, 0, $final_string_length) ."...";
			 }
		 }
		 return $final_string;
	 }
     static public function index_of_string($string, $sub_string){
		 $string_array = explode("", $string);
		 $index = -1;
		 if(is_array($string_array)){
			 for($x = 0; $x < count($string_array); $x++){
				 if($string_array[$x] == $sub_string){
					 $index = $x;
					 break;
				 }
			 }
		 }
		 return $index;
	 }
	 public static function random_string2($length = 8, $base64_encode = false, $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@#$%^&*"){
		 if (!is_int($length) || $length < 0){
			 return false;
		 }
		 $characters_length = strlen($characters) - 1;
		 $string = "";
		 for ($i = $length; $i > 0; $i--){
			 $string .= $characters[mt_rand(0, $characters_length)];
		 }
         return $base64_encode == true ? base64_encode($string) : trim($string);
	 }
	 //construct a string from an array of elements:
	 static public function construct_string_from_array(array $array, $separator, $finisher = null, $and = false){
		 $clean_array = array();
		 foreach($array as $el){
			 if($el != "") array_push($clean_array, $el);
		 }
		 if(count($clean_array) == 0) return "";
		 if(count($clean_array) == 1 && !is_null($finisher)) return trim($clean_array[0]).$finisher;
		 if(count($clean_array) == 1 && is_null($finisher)) return trim($clean_array[0]);
		 $constructed_string = ""; //the string to be returned:
		 for($s = 0; $s < count($clean_array); $s++){ //for every element of the array:
			 if($s == 0) $constructed_string = $clean_array[$s];
			 if($s != 0 && $s != count($clean_array) - 1) $constructed_string .= $separator .$clean_array[$s];
			 if($s != 0 && $s == count($clean_array) - 1 && $and == false) $constructed_string .= $separator .$clean_array[$s];
			 if($s != 0 && $s == count($clean_array) - 1 && $and == true) $constructed_string .= " and " .$clean_array[$s];
		 }
		 if(!is_null($finisher))$constructed_string .= $finisher; //if the finisher is available, append it to the string:
		 return $constructed_string; //return the constructed string:
	 }
	 /*construct a string from an array of objects:
		- $array: the array of objects to construct a string from.
		- $separator: the character to place between the elements to be constructed.
		- $finisher: the character to append to the string.
		- $property: the property of each object in the array to use in constructing a string.
	 */
     static public function construct_string_from_array_objects($array, $separator, $finisher = null, $property = null){
         $constructed_string = "";
         for($s = 0; $s < count($array); $s++){
			 if($array[$s] != ""){
				 if($s == 0){
					 $constructed_string = $array[$s];
				 }else{
					 $constructed_string .= $separator .$array[$s];
				 }
			 }else{
				 continue;
			 }
         }
         if(!is_null($finisher)){
             $constructed_string .= $finisher;
         }
         return $constructed_string;
	 }
	 //add a specified number of leading zeroes to a string.
	 static public function pre_pend_leading_zeros($str_val, $totalLength){
		 $newstr = ""; //the string that will be returned.
		 $str_len = strlen($str_val); //find the length of the input string.
		 $zeros = $totalLength - $str_len; //the number of zeroes to prepend.
		 for($x = 0; $x < $zeros; $x++)
		 {
			 $newstr = $newstr."0";
		 }
		 $newstr = $newstr.$str_val;
		 return $newstr; //return the new string.
	 }
	 public static function slugify($text){
         //replace non letter or digits by -
         $text = preg_replace('~[^\pL\d]+~u', '-', $text);
         //transliterate
         $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
         //remove unwanted characters
         $text = preg_replace('~[^-\w]+~', '', $text);
         //trim
         $text = trim($text, '-');
         //remove duplicate -
         $text = preg_replace('~-+~', '-', $text);
         //lowercase
         $text = strtolower($text);
         if(empty($text)){
             return 'n-a';
         }
         return $text;
     }
     public function extract_hashtags($text){
         preg_match_all('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', $text, $matched_hashtags);
         $hashtag = '';
         if(!empty($matched_hashtags[0])){
             foreach($matched_hashtags[0] as $match){
                 $hashtag .= preg_replace("/[^a-z0-9]+/i", "", $match).',';
             }
         }
         return rtrim($hashtag, ',');
	 }
	 public function extract_mentions($text){
         preg_match_all('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', $text, $matched_mentions);
         $mentions = '';
         if(!empty($matched_mentions[0])){
             foreach($matched_mentions[0] as $match){
                 $mentions .= preg_replace("/[^a-z0-9]+/i", "", $match).',';
             }
         }
         return rtrim($mentions, ',');
	 }
	 public function convert_hashtags_to_links($text, $base_url, $new_window = true){
		 $replacement = $new_window ? "<a target='_blank' href='".$base_url."$1'>#$1</a>" : "<a href='".$base_url."$1'>#$1</a>";
		 return preg_replace("/#([A-Za-z0-9\/\.]*)/", $replacement, $text);
	 }
	 public function convert_urls_to_links($text){
		 return preg_replace("/([\w]+\:\/\/[\w\-?&;#~=\.\/\@]+[\w\/])/", "<a target=\"_blank\" href=\"$1\">$1</a>", $text);
	 }
	 public function convert_mentions_to_links($text, $base_url, $mentioned_persons){
		 $text_array = explode("\n", $text);
		 $convertedtext_array = array();
		 foreach($text_array as $t){
			 array_push($convertedtext_array,
			 preg_replace_callback("/(^|[^a-z0-9_])@([a-z0-9_]+)/i",  function($matches) use ($base_url, $mentioned_persons){
				 $user_name = substr(trim($matches[0]), 1);
				 $mentioned_person = null;
				 for($m = 0; $m < count($mentioned_persons); $m++){
					 if($mentioned_persons[$m]->user_name == $user_name){
						 $mentioned_person = $mentioned_persons[$m];
						 break;
					 }
				 }
				 if(!is_null($mentioned_person)){
					 return " <a href='".$base_url.$mentioned_person->user_name."/".$mentioned_person->user_id."'><span class='post_mention'>".$matches[0]."</span></a> ";
				 }else{
					 return $matches[0];
				 }
			 }, $t));
		 }
		 return implode("\n", $convertedtext_array);
	 }
	 public static function guid(){
         if(function_exists('com_create_guid') === true){
             return trim(com_create_guid(), '{}');
         }
         return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
     }
     public static function get_property_value(string $prop, ?stdClass $object = null, string $sep = '.'){
         return array_reduce(explode($sep, $prop), function($previous, $current){
             return is_numeric($current) ? ($previous[$current] ?? null) : ($previous->$current ?? null); 
         }, $object);
     }
     public function set_template_context($template, $context_values = []){
		 foreach($context_values as $key => $value){
			 if(!is_null($value))$template = str_replace('{{ '.$key.' }}', $value, $template);
		 }
		 return $template;
	 }

     public function encrypt($plain_text, $key){
        $secret_key = md5($key);
        $iv = substr(hash('sha256', "aaaabbbbcccccddddeweee"), 0, 16);
        $encrypted_text = openssl_encrypt($plain_text, 'AES-128-CBC', $secret_key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypted_text);
    }

     public function decrypt($encrypted_text, $key){
        $key = md5($key);
        $iv = substr(hash('sha256', "aaaabbbbcccccddddeweee" ), 0, 16);
        $decrypted_text = openssl_decrypt(base64_decode($encrypted_text), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $decrypted_text;
     }
}
?>