<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Core\Support\CropMode;
use Closure;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class MediaField extends FileField {
	 
	 protected string $media_type;

	 //the maximum width
	 #[ShouldValidate()]
	 protected mixed $max_width = null;

	 //the minimum width
	 #[ShouldValidate()]
	 protected mixed $min_width = null;

	 //the width of the image
	 #[ShouldValidate()]
	 protected mixed $width = null;

	 //the maximum height of image
	 #[ShouldValidate()]
	 protected mixed $max_height = null;

	 //the miniumum height of image
	 #[ShouldValidate()]
	 protected mixed $min_height = null;

	 //the height of image
	 #[ShouldValidate()]
	 protected mixed $height = null;

	 /**
	  * Image aspect ratio
	  * 
	  * Example: [16, 9], [1, 1]
	  * */
	 #[ShouldValidate()]
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

	 public function max_width(mixed $max_width){
	 	 $this->max_width = $max_width;
	 	 return $this;
	 }

	 public function min_width(mixed $min_width){
	 	 $this->min_width = $min_width;
	 	 return $this;
	 }

	 public function width(mixed $width){
	 	 $this->width = $width;
	 	 return $this;
	 }

	 public function max_height(mixed $max_height){
	 	 $this->max_height = $max_height;
	 	 return $this;
	 }

	 public function min_height(mixed $min_height){
	 	 $this->min_height = $min_height;
	 	 return $this;
	 }

	 public function height(mixed $height){
	 	 $this->height = $height;
	 	 return $this;
	 }

	 public function aspect_ratio(array $aspect_ratio){
	 	 $this->aspect_ratio = $aspect_ratio;
	 	 return $this;
	 }

	 public function get_max_width(){
	 	 return $this->max_width;
	 }

	 public function get_min_width(){
	 	 return $this->min_width;
	 }

	 public function get_width(){
	 	 return $this->width;
	 }

	 public function get_max_height(){
	 	 return $this->max_height;
	 }

	 public function get_min_height(){
	 	 return $this->min_height;
	 }

	 public function get_height(){
	 	 return $this->height;
	 }

	 public function get_aspect_ratio(){
	 	 return $this->aspect_ratio;
	 }

	 public function get_media_type(){
	 	 return $this->media_type;
	 }

	 protected function validate_field_state(){
	 	 if($this->height && $this->max_height && ($this->height !== $this->max_height)){
	 	 	 $this->errors[] = "Having height and maximum height at the same time is ambigous!";
	 	 }

	 	 if($this->height && $this->min_height && ($this->height !== $this->min_height)){
	 	 	 $this->errors[] = "Having height and minimum height at the same time is ambigous!";
	 	 }

	 	 if($this->width && $this->max_width && ($this->width !== $this->max_width)){
	 	 	 $this->errors[] = "Having width and maximum width at the same time is ambigous!";
	 	 }

	 	 if($this->width && $this->min_width && ($this->width !== $this->min_width)){
	 	 	 $this->errors[] = "Having width and minimum width at the same time is ambigous!";
	 	 }

	 	 if($this->max_height && $this->min_height && ($this->min_height > $this->max_height)){
	 	 	 $this->errors[] = "Minimum height cannot be more than the maximum height!";
     	 }

     	 if($this->max_width && $this->min_width && ($this->min_width > $this->max_width)){
     	 	 $this->errors[] = "Minimum width cannot be more than the maximum width!";
     	 }

     	 parent::validate_field_state();
	 }
}

