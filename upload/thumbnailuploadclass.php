<?php
namespace SaQle\Uploads;
require_once "fileuploaderclass.php";
require_once "thumbnailimageclass.php";
class ThumbnailUpload extends Uploader{
	 protected $thumb_destination;
	 protected $delete_original;
	 protected $suffix = '_thb';
	 public function __construct($path, $delete_original = false){
		 parent::__construct($path);
		 $this->thumb_destination = $path;
		 $this->delete_original = $delete_original;
	 }
	 public function setthumb_destination($path){
		 if (!is_dir($path) || !is_writable($path)){
			 throw new \Exception("$path must be a valid, writable directory..");
		 }
		 $this->thumb_destination = $path;
	 }
	 public function setThumbSuffix($suffix){
		 if(preg_match('/\w+/', $suffix)){
			 if(strpos($suffix, '_') !== 0){
				 $this->suffix = '_' . $suffix;
			 }else{
				 $this->suffix = $suffix;
			 }
		 }else{
			 $this->suffix = '';
		 }
	 }
	 public function allow_all_types($suffix = true){
		 $this->check_type = true;
	 }
	 //the following function creates a single thumbnail:
	 protected function create_thumbnail($image, $width, $height){
		 $thumb = new Thumbnail($image);
		 $thumb->set_destination($this->thumb_destination);
		 $thumb->set_suffix($this->suffix);
		 $thumb->set_max_size($width);
		 $thumb->create($width, $height);
		 $messages = $thumb->get_messages();
		 $this->messages = array_merge($this->messages, $messages);
	 }
	 //the following function creates multiple thumbnails:
	 protected function create_multiple_thumbnail($image, $array_of_sizes){
		 $thumb = new Thumbnail($image);
		 $thumb->set_destination($this->thumb_destination);
		 $thumb->set_suffix($this->suffix);
		 $thumb->create();
		 $messages = $thumb->get_messages();
         $this->messages = array_merge($this->messages, $messages);
	 }
	 //move file to its desired destination and create a thumbnail:
	 public function move_file($file, $thumb_sizes, $thumbs_folder){
		 $filename = isset($this->new_name) ? $this->new_name : $file['name'];
		 $success = move_uploaded_file($file['tmp_name'], $this->destination . $filename);
		 if($success){
			 //add a message only if the original image is not deleted
			 if(!$this->delete_original){
				 $result = $file['name'] . ' was uploaded successfully';
				 if(!is_null($this->new_name)){
					 $result .= ', and was renamed ' . $this->new_name;
				 }
				 $this->messages[] = $result;
			 }
			 for($s = 0; $s < count($thumb_sizes); $s++){
				 $this->thumb_destination = $thumbs_folder."thumbs_".$thumb_sizes[$s][0]."x".$thumb_sizes[$s][1];
				 if(!file_exists($this->thumb_destination)){
					 mkdir($this->thumb_destination);
				 }
				 // create a thumbnail from the uploaded image
			     $this->create_thumbnail($this->destination . $filename, $thumb_sizes[$s][0], $thumb_sizes[$s][1]);
			 }
			 //delete the uploaded image if required
			 if($this->delete_original){
				 unlink($this->destination . $filename);
			 }
		 }else{
			 $this->messages[] = 'Could not upload ' . $file['name'];
		 }
	 }
}
?>