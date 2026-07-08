<?php
namespace SaQle\Image;
class Image{
	 private $ext;
	 private function create_original_image_resource($file){
		 $size = getimagesize($file);
		 $this->ext = $size['mime'];
	     switch($this->ext){
	         case 'image/jpg':
	         case 'image/jpeg':
	             return imagecreatefromjpeg($file);
	         break;
	         case 'image/gif':
	             return imagecreatefromgif($file);
	         break;
	         case 'image/png':
	             return imagecreatefrompng($file);
	         break;
	         default:
	             return null;
			 break;
	     }
	 }
	 private function save_image($image_resource, $destination_folder, $quality = 90, $download = false){
	     switch($this->ext){
	         case 'image/jpg':
	         case 'image/jpeg':
	             if(imagetypes() & IMG_JPG) imagejpeg($image_resource, $destination_folder, $quality);
	         break;
	         case 'image/gif':
	             if(imagetypes() & IMG_GIF) imagegif($image_resource, $destination_folder);
	         break;
	         case 'image/png':
	            if(imagetypes() & IMG_PNG)  imagepng($image_resource, $destination_folder, 9);
	         break;
	     }
	     if($download){
	    	 header('Content-Description: File Transfer');
		     header("Content-type: application/octet-stream");
		     header("Content-disposition: attachment; filename= ".$destination_folder."");
		     readfile($destination_folder);
	     }
	     imagedestroy($image_resource);
	 }
	 public function resize_image($file, $max_resolution, $destination_folder = null, $min_resolution = null){
		 if(file_exists($file)){
			 $original_image = $this->create_original_image_resource($file);
			 //resolution
			 $original_width = imagesx($original_image);
			 $original_height = imagesy($original_image);
			 
			 //try width first:
			 $ratio = $max_resolution / $original_width;
			 $new_width = $max_resolution;
			 $new_height = $original_height * $ratio;
			 
			 //if that didnt work
			 if($new_height > $max_resolution){
				 $ratio = $max_resolution / $original_height;
				 $new_height = $max_resolution;
				 $new_width = $original_width * $ratio;
			 }
			 if($original_image){
				 $new_image = imagecreatetruecolor((int)$new_width, (int)$new_height);
				 imagecopyresampled($new_image, $original_image, 0, 0, 0, 0, (int)$new_width, (int)$new_height, $original_width, $original_height);
				 $destination_folder = !is_null($destination_folder) ? $destination_folder : $file;
				 $this->save_image($new_image, $destination_folder);
			 }
		 }
	 }
	 
	 public function crop_image($file, $max_resolution, $destination_folder = null, $min_resolution = null){
		 if(file_exists($file)){
			 $original_image = $this->create_original_image_resource($file);
			 //resolution
			 $original_width = imagesx($original_image);
			 $original_height = imagesy($original_image);
			 //try width first:
			 if($original_height > $original_width){
				 $ratio = $max_resolution / $original_width;
			     $new_width = $max_resolution;
			     $new_height = $original_height * $ratio;
				 
				 $diff = $new_height - $new_width;
				 $x = 0;
				 $y = round($diff / 2);
			 }else{
				 $ratio = $max_resolution / $original_height;
				 $new_height = $max_resolution;
				 $new_width = $original_width * $ratio;
				 
				 $diff = $new_width - $new_height;
				 $x = round($diff / 2);
				 $y = 0;
			 }
			 if($original_image){
				 $new_image = imagecreatetruecolor((int)$new_width, (int)$new_height);
				 imagecopyresampled($new_image, $original_image, 0, 0, 0, 0, (int)$new_width, (int)$new_height, $original_width, $original_height);
				 $new_crop_image = imagecreatetruecolor($max_resolution, $max_resolution);
				 imagecopyresampled($new_crop_image, $new_image, 0, 0, $x, $y, $max_resolution, $max_resolution, $max_resolution, $max_resolution);
				 $destination_folder = !is_null($destination_folder) ? $destination_folder : $file;
				 $this->save_image($new_crop_image, $destination_folder);
			 }
		 }
	 }
}
