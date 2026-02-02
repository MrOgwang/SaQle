<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Core\Support\CropMode;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class ImageField extends MediaField {
	
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
	 	 $kwargs['mime_types'] = $kwargs['mime_types'] ?? ['image/*'];
	 	 parent::__construct(...$kwargs);
	 }

	 public function resize(array $rules){
	 	 $this->resize = $rules;
	 	 return $this;
	 }

	 public function get_resize(){
	 	 return $this->resize;
	 }

	 public function crop(CropMode $mode){
	 	 $this->crop = $mode;
	 	 return $this;
	 }

	 public function get_crop(){
	 	 return $this->crop;
	 }
}

