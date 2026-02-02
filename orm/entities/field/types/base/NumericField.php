<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class NumericField extends Field {

	 //the minimum value allowed
	 protected mixed $min = null;

	 //the maximum value allowed
	 protected mixed $max = null;

	 //disallow negative numbers
	 protected bool $unsigned = false;

	 //increment step
	 protected mixed $step = null;

	 //display format
	 protected ?string $format = null;

	 //whether to auto incerement
	 #[FieldDefinition()]
	 protected bool $auto = false;

	 public function auto(bool $auto = true){
	 	 $this->auto = $auto;
	 	 return $this;
	 }

	 public function min(mixed $min){
	 	 $this->min = $min;
	 	 return $this;
	 }

	 public function max(mixed $max){
	 	 $this->max = $max;
	 	 return $this;
	 }

	 public function unsigned(bool $unsigned = true){
	 	 $this->unsigned = $unsigned;
	 	 return $this;
	 }

	 public function step(mixed $step){
	 	 $this->step = $step;
	 	 return $this;
	 }

	 public function format(string $format){
	 	 $this->format = $format;
	 	 return $this;
	 }

	 public function get_auto(){
	 	 return $this->auto;
	 }

	 public function get_min(){
	 	 return $this->min;
	 }

	 public function get_max(){
	 	 return $this->max;
	 }

	 public function get_unsigned(){
	 	 return $this->unsigned;
	 }

	 public function get_step(){
	 	 return $this->step;
	 }

	 public function get_format(){
	 	 return $this->format;
	 }
}

