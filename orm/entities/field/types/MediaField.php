<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Core\Support\CropMode;
use Closure;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

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
}

