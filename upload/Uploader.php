<?php
namespace SaQle\Upload;
class Uploader{
	 private $check_type = true;
	 private $file_name;
	 protected $destination;
	 private $file_data;
     private $max = 51200;
	 private $messages = [];
	 private $permitted = ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'];
	 private $not_trusted = ['bin', 'cgi', 'exe', 'js', 'pl', 'php', 'py', 'sh']; //file extensions that are potentially danerous:
	 private $suffix = '.dangerous';
	 protected $new_name;
	 private $rename_duplicates;
	 public function __construct($path){
		 if(!is_dir($path) || !is_writable($path)) {
			throw new \Exception("$path must be a valid, writable directory.");
		 }
		 $this->destination = $path;
	 }
     public function upload($rename_duplicates = true){
		 $this->rename_duplicates = $rename_duplicates;
		 $uploaded = current($_FILES);
		 if(is_array($uploaded['name'])){
			 foreach ($uploaded['name'] as $key => $value){
				 $current_file['name'] = $uploaded['name'][$key];
				 $current_file['type'] = $uploaded['type'][$key];
				 $current_file['tmp_name'] = $uploaded['tmp_name'][$key];
				 $current_file['error'] = $uploaded['error'][$key];
				 $current_file['size'] = $uploaded['size'][$key];
				 if($this->check_file($current_file)){
					 $this->move_file($current_file);
				 }
			}
		 }else{
			 if($this->check_file($uploaded)){
				 $this->move_file($uploaded);
			 }
		 }
	 }
	 public function allow_all_types($suffix = true){
		 $this->check_type = false;
		 if(!$suffix){
			 $this->suffix = '';
		 }
	 }
	 protected function check_file($file){
		 $accept = true;
		 if($file['error'] != 0){
			 $this->get_error_message($file);
			 if($file['error'] == 4){//no file has been submitted.
				 return false;
			 }else{
				 $accept = false;
			 }
		 }
		 if(!$this->check_size($file)){
			 $accept = false;
		 }
		 if($this->check_type){
			 if(!$this->check_type($file)){
				$accept = false;
			 }
		 }
		 if($accept){
			 $this->check_name($file);
		 }
		 return $accept;
	 }
	 protected function check_name($file){
		 $this->new_name = null;
		 $nospaces = str_replace(' ', '_', $file['name']);
		 if($nospaces != $file['name']){
			 $this->new_name = $nospaces;
		 }
		 $extension = pathinfo($nospaces, PATHINFO_EXTENSION);
		 if(!$this->check_type && !empty($this->suffix)){
			 if(in_array($extension, $this->not_trusted) || empty($extension)){
				 $this->new_name = $nospaces . $this->suffix;
			 }
		 }
		 if($this->rename_duplicates){
			 $name = isset($this->new_name) ? $this->new_name : $file['name'];
			 $existing = scandir($this->destination);
			 if(in_array($name, $existing)){
				 $basename = pathinfo($name, PATHINFO_FILENAME);
				 $extension = pathinfo($name, PATHINFO_EXTENSION);
				 $i = 1;
				 do{
					 $this->new_name = $basename . '_' . $i++;
					 if(!empty($extension)){
						 $this->new_name .= ".$extension";
					 }
				 }while(in_array($this->new_name, $existing));
			 }
		 }
	 }
	 public function set_max_size($num){
		 if(is_numeric($num) && $num > 0){
			 $this->max = (int)$num;
		 }
	 }
	 protected function move_file($file, $width, $height){
		 $file_name = isset($this->new_name) ? $this->new_name : $file['name'];
		 $success = move_uploaded_file($file['tmp_name'], $this->destination . $file_name);
		 if($success){
			 $result = $file['name'] . ' was uploaded successfully';
			 if(!is_null($this->new_name)){
				 $result .= ', and was renamed ' . $this->new_name;
			 }
			 $this->messages[] = $result;
		 }else{
			 $this->messages[] = 'Could not upload ' . $file['name'];
		 }
	 }
	 public function get_messages(){
		 return $this->messages;
	 }
	 protected function get_error_message($file){
		 switch($file['error']){
			 case 1:
			 case 2:
			     $this->messages[] = $file['name'] . ' is too big: (max: ' .$this->get_max_size() . ').';
			 break;
			 case 3:
			     $this->messages[] = $file['name'] . ' was only partially uploaded.';
			 break;
			 case 4:
			     $this->messages[] = 'No file submitted.';
		     break;
			 default:
			     $this->messages[] = 'Sorry, there was a problem uploading ' .$file['name'];
			 break;
		 }
	 }
	 protected function check_size($file){
		 if($file['error'] == 1 || $file['error'] == 2){
			 return false;
		 }elseif ($file['size'] == 0){
			 $this->messages[] = $file['name'] . ' is an empty file.';
			 return false;
		 }elseif($file['size'] > $this->max){
			 $this->messages[] = $file['name'] . ' exceeds the maximum size for a file (' . $this->get_max_size() . ').';
			 return false;
		 }else{
			 return true;
		 }
	 }
	 protected function check_type($file){
		 if(in_array($file['type'], $this->permitted)){
			 return true;
		 }else{
			 if(!empty($file['type'])){
				 $this->messages[] = $file['name'] . ' is not permitted type of file.';
			 }
			 return false;
		 }
	 }
	 public function get_max_size(){
		 return number_format($this->max/1024, 1). ' KB';
	 }
}
