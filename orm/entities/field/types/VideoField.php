<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Core\Support\CropMode;
use SaQle\Orm\Entities\Field\Attributes\{FieldDefinition, ShouldValidate};

class VideoField extends MediaField {

	 //duration in seconds
	 #[ShouldValidate()]
     protected ?int $min_duration = null;

     #[ShouldValidate()]
     protected ?int $max_duration = null;

     #[ShouldValidate()]
     protected ?int $duration = null;

	 protected function initialize_defaults(){

         if(!$this->mime_types){
         	 $this->mime_types = ['video/*'];
         }

         $this->media_type = "video";

         parent::initialize_defaults();

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

	 protected function validate_field_state(){
	 	 if($this->duration && $this->max_duration && ($this->duration !== $this->max_duration)){
	 	 	 $this->errors[] = "Having duration and maximum duration at the same time is ambigous!";
	 	 }

	 	 if($this->duration && $this->min_duration && ($this->duration !== $this->min_duration)){
	 	 	 $this->errors[] = "Having duration and minimum duration at the same time is ambigous!";
	 	 }

	 	 if($this->max_duration && $this->min_duration && ($this->min_duration > $this->max_duration)){
	 	 	 $this->errors[] = "Minimum duration cannot be more than the maximum duration!";
     	 }

     	 parent::validate_field_state();
	 }
}

