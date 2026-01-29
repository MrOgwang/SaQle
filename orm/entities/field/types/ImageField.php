<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Core\Support\CropMode;

class ImageField extends FileField {
	 
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
	  * an array of image resize rules. Contains the following keys
	  * 
	  * max_width  : the upper limit for width
	  * max_height : the upper limit for height
	  * no_upscale : whether to enlarge the image when resizing. Defaults t o true
	  * */
	 protected ?array $resize = null;

	 //the crop mode
	 protected CropMode $crop = CropMode::NONE;

	 public function __construct(...$kwargs){
	 	 $kwargs['image_only'] = true;
	 	 $kwargs['mime_types'] = $kwargs['mime_types'] ?? ['image/*'];
	 	 parent::__construct(...$kwargs);
	 }
}

