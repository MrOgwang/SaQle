<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Core\Support\CropMode;
use Closure;

class MediaField extends FileField {
	 
	 //the maximum width
	 protected mixed $max_width = null;

	 //the minimum width
	 protected mixed $min_width = null;

	 //the width of the image
	 protected mixed $width = null;

	 //the maximum height of image
	 protected mixed $max_height = null;

	 //the miniumum height of image
	 protected mixed $min_height = null;

	 //the height of image
	 protected mixed $height = null;

	 /**
	  * Image aspect ratio
	  * 
	  * Example: [16, 9], [1, 1]
	  * */
	 protected ?array $aspect_ratio = null;

	 /**
      * Where there were no files uploaded, but there is a default file that can be shown, return its path
      * 
      * This is particularly useful for images and videos
      * 
      * */
	 protected null|string|Closure $default_url = null;

	 public function default_url(string|callable $url){
	 	 $this->default_url = $url;
	 	 return $this;
	 }

	 public function get_default_url(){
	 	 return $this->default_url;
	 }
}

