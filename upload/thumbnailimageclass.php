<?php
namespace SaQle\Uploads;
class ThumbNail{
	 protected $original;
	 protected $originalwidth; //original width of image:
	 protected $originalheight; //original height of image:
	 protected $basename; //name of image:
	 protected $thumbwidth; //width of thumb image:
	 protected $thumbheight; //height of thumb image:
	 protected $maxsize = 120;
	 protected $canprocess = false;
	 protected $imagetype;
	 protected $destination; //destination folder of image:
	 protected $suffix = '_thb';
	 protected $messages = [];
	 public function __construct($image){ //takes an image path as an argument:
		 if(is_file($image) && is_readable($image)){
			 $details = getimagesize($image);
		 }else{
			 $details = null;
			 $this->messages[] = "Cannot open $image.";
		 }
		 //if getimagesize() returns an array, it looks like an image
		 if(is_array($details)){
			 $this->original = $image;
			 $this->originalwidth = $details[0];
			 $this->originalheight = $details[1];
			 $this->basename = pathinfo($image, PATHINFO_FILENAME);
			 // check the MIME type
			 $this->check_type($details['mime']);
		 }else{
			 $this->messages[] = "$image doesn't appear to be an image.";
		 }
	 }
	 public function get_messages(){
		 return $this->messages;
	 }
	 //the following function checks the mime type of the image:
	 protected function check_type($mime){
		 $mimetypes = ['image/jpeg', 'image/png', 'image/gif'];
		 if(in_array($mime, $mimetypes)){
			 $this->canprocess = true;
			 // extract the characters after 'image/'
			 $this->imagetype = substr($mime, 6);
		 }
	 }
	 //the following function sets the destination where the thumbnail produced would be saved:
	 public function set_destination($destination){
		 if(is_dir($destination) && is_writable($destination)){ //checks whether the name provided is a valid directory and is writable:
			 //get last character
			 $last = substr($destination, -1);
			 //add a trailing slash if missing
			 if($last == '/' || $last == '\\'){
				 $this->destination = $destination;
			 }else{
				 $this->destination = $destination . DIRECTORY_SEPARATOR;
			 }
		 }else{
			 $this->messages[] = "Cannot write to $destination.";
		 }
	 }
     //the following function sets the maximum size of the thumbnail image:
     public function setmaxsize($size){
	     if(is_numeric($size)){//check if the size provided is really a number:
		     $this->maxsize = abs($size); //this number is passed to the abs function, as a precaution to change the max size into positive incase its negative:
	     }
     }
	 //the following function sets the suffix of the newly created image:
	 public function set_suffix($suffix){
		 if(preg_match('/^\w+$/', $suffix)){
			 if(strpos($suffix, '_') !== 0){
				 $this->suffix = '_' . $suffix;
			 }else{
				 $this->suffix = $suffix;
			 }
		 }else{
			 $this->suffix = '';
		 }
	 }
	 //the following function will calculate the new dimensions of the thumbnail image:
	 protected function calculate_size($width, $height){
		 if($width <= $this->maxsize && $height <= $this->maxsize){
			$ratio = 1;
		 }elseif($width > $height){
			 $ratio = $this->maxsize/$width;
		 }else{
			 $ratio = $this->maxsize/$height;
		 }
		 $this->thumbwidth = round($width * $ratio);
		 $this->thumbheight = round($height * $ratio);
	 }
	 //the following function will set the width and heights of the thumbnails:
	 protected function set_thumb_dimensions($width, $height){
		$this->thumbwidth = $width;
		$this->thumbheight = $height;
	 }
	 //the following function will now create the thumbnail image:
	 public function create($width, $height){
		 if($this->canprocess && $this->originalwidth != 0){
			 $this->calculate_size($this->originalwidth, $this->originalheight);
			 //$this->set_thumb_dimensions($width, $height);
			 $this->createThumbnail();
		 }elseif($this->originalwidth == 0){
			 $this->messages[] = 'Cannot determine size of ' . $this->original;
		 }
	 }
	 //the following function will create the image resource for the original image:
	 protected function create_image_resource(){
		 if($this->imagetype == 'jpeg'){
			 return imagecreatefromjpeg($this->original);
		 }elseif ($this->imagetype == 'png'){
			 return imagecreatefrompng($this->original);
		 }elseif ($this->imagetype == 'gif'){
			 return imagecreatefromgif($this->original);
		 }
	 }
	 //the following function will create the image resource for the thumbnail image:
	 protected function createThumbnail(){
		 $resource = $this->create_image_resource();
		 $thumb = imagecreatetruecolor($this->thumbwidth, $this->thumbheight);
		 imagecopyresampled($thumb, $resource, 0, 0, 0, 0, $this->thumbwidth, $this->thumbheight, $this->originalwidth, $this->originalheight);
		 //the newly created thumbnail will now need to be saved to destination:
		 $newname = $this->basename . $this->suffix;
		 if($this->imagetype == 'jpeg'){
			 $newname .= '.jpg';
			 $success = imagejpeg($thumb, $this->destination . $newname, 100);
		 }elseif($this->imagetype == 'png'){
			 $newname .= '.png';
			 $success = imagepng($thumb, $this->destination . $newname, 0);
		 }elseif($this->imagetype == 'gif'){
			 $newname .= '.gif';
			 $success = imagegif($thumb, $this->destination . $newname);
		 }
		 if($success){
			 $this->messages[] = "$newname created successfully.";
		 }else{
			 $this->messages[] = "Couldn't create a thumbnail for " .
			 basename($this->original);
		 }
		 //release memory resources after the thumbnail has been created:
		 imagedestroy($resource);
		 imagedestroy($thumb);
	 }
}
?>