<?php
namespace SaQle\DirManager;

use SaQle\Commons\Commons;

class DirManager{
	 use Commons;
	 const DIR_PERMISSION = 0755;
	 //user folders
	 const USER_PERSONAL_VIDEOS_DIR = "{{ tenant_folder }}/users/personal/{{ user_id }}/videos/";
	 const USER_PERSONAL_IMAGES_DIR = "{{ tenant_folder }}/users/personal/{{ user_id }}/images/";
	 const USER_PERSONAL_GENERAL_DIR_ = "{{ tenant_folder }}/users/personal/{{ user_id }}/general/";
	 //general folders.
	 const GENERAL = "{{ tenant_folder }}/general/";
	 const TEMPORARY = "{{ tenant_folder }}/tmp/";

	 private function get_media_folder(){
	 	return HIDDEN_MEDIA_FOLDER ? dirname($_SERVER['DOCUMENT_ROOT']).'/'.MEDIA_FOLDER.'/' : DOCUMENT_ROOT.'/'.MEDIA_FOLDER.'/';
	 }

	 protected function create_dir($dir, $root = null){
		 $abs_path = $root ? $root.$dir : $this->get_media_folder().$dir;
		 if(!file_exists($abs_path)){
		 	 $oldumask = umask(0);
		 	 mkdir($abs_path, self::DIR_PERMISSION, true);
		 	 umask($oldumask);
		 }
		 return $abs_path;
	 }
	 protected function replace_context_values($context_values, $subject){
		 foreach($context_values as $key => $value){
			 $subject = str_replace('{{ '.$key.' }}', $value, $subject);
		 }
		 return $subject;
	 }
	 public function get_user_personal_dir($tenant_token, $user_id, $type = null){
		 $folder = match($type){
             'videos' => $this->replace_context_values(['user_id' => $user_id, 'tenant_folder' => $tenant_token], self::USER_PERSONAL_VIDEOS_DIR),
             'images' => $this->replace_context_values(['user_id' => $user_id, 'tenant_folder' => $tenant_token], self::USER_PERSONAL_IMAGES_DIR),
             default => $this->replace_context_values(['user_id' => $user_id, 'tenant_folder' => $tenant_token], self::USER_PERSONAL_GENERAL_DIR_),
         };
		 return $this->create_dir($folder);
	 }
	 public function get_general_dir($tenant_token){
		 $dir = $this->replace_context_values(array("tenant_folder"=>$tenant_token), self::GENERAL);
		 return $this->create_dir($dir);
	 }
	 public function get_tmp_dir($tenant_token){
		 $dir = $this->replace_context_values(array("tenant_folder"=>$tenant_token), self::TEMPORARY);
		 return $this->create_dir($dir);
	 }
	 public function get_random_file_name($file_extension){
		 return (new class { use \SaQle\Commons\Commons; })::random_string(max_length: 10, min_length: 5).".".$file_extension;
	 }
	 public function get_tmp_file_name($tenant_token, $file_extension){
		 $tmp_dir = $this->get_tmp_dir($tenant_token);
		 $file_path = $tmp_dir.$this->get_random_file_name($file_extension);
		 while(file_exists($file_path)){
			 $file_path = $tmp_dir.$this->get_random_file_name($file_extension);
		 }
		 return $file_path;
	 }
}
?>