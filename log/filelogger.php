<?php
namespace SaQle\Log;

interface ReadWriteModes{
	 const START_READ_ONLY = "r";
	 const START_READ_WRITE = "r+";
	 const CLEAR_WRITE_ONLY = "w";
	 const CLEAR_READ_WRITE = "w+";
	 const APPEND_WRITE_ONLY = "a";
	 const APPEND_READ_WRITE = "a+";
	 const INSTANCE_WRITE_ONLY = "x";
	 const INSTANCE_READ_WRITE = "x+";
}
class FileLogger implements ReadWriteModes{
	 private $file_path;
	 private $file_mode;
	 public function __construct($file_path, $file_mode){
		 $this->file_path = $file_path;
		 $this->file_mode = $file_mode;
	 }
	 public function log_to_file($file_contents){
		 if($this->file_mode !== self::START_READ_ONLY){
			 try{
			     $file_handle = fopen($this->file_path, $this->file_mode);
			     fwrite($file_handle, $file_contents);
			     fclose($file_handle);
		     }catch(Exception $ex){
			     echo $ex;
		     }
		 }
	 }
	 public function read_from_file($read_by_line = true){
		 $file_read_modes = [
		     self::START_READ_ONLY, 
			 self::START_READ_WRITE, 
			 self::CLEAR_READ_WRITE, 
			 self::APPEND_READ_WRITE, 
			 self::INSTANCE_READ_WRITE
		 ];
		 $file_contents = "";
		 if(in_array($this->file_mode, $file_read_modes)){
			 try{
			     $file_handle = fopen($this->file_path, $this->file_mode);
				 if($read_by_line){
					 $file_contents = array();
			         while($line = fgets($file_handle)){  
		                 array_push($file_contents, str_replace("\n", "", $line));
		             }
		         }else{
					 $file_contents = file_get_contents($this->file_path);
				 }
			     fclose($file_handle);
		     }catch(Exception $ex){
			     echo $ex;
		     }
		 }
		 return $file_contents;
	 }
}
