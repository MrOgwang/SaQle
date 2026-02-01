<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Core\Support\CropMode;

class VideoField extends MediaField {

	 //duration in seconds
     protected ?int $min_duration = null;
     protected ?int $max_duration = null;
     protected ?int $duration = null;

	 public function __construct(...$kwargs){
	 	 $kwargs['mime_types'] = $kwargs['mime_types'] ?? ['video/*'];
	 	 parent::__construct(...$kwargs);
	 }

	 public function min_duration(int $min_duration){
	 	 $this->min_duration = $min_duration;
	 	 return $this;
	 }

	 public function get_min_duration(){
	 	 return $this->min_duration;
	 }

	 public function max_duration(int $max_duration){
	 	 $this->max_duration = $max_duration;
	 	 return $this;
	 }

	 public function get_max_duration(){
	 	 return $this->max_duration;
	 }

	 public function duration(int $duration){
	 	 $this->duration = $duration;
	 	 return $this;
	 }

	 public function get_duration(){
	 	 return $this->duration;
	 }
}

