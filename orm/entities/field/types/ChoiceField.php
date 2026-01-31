<?php

namespace SaQle\Orm\Entities\Field\Types;

class ChoiceField extends CharField {
	 //the choices to pick from
	 protected ?array $choices = null;

	 //whether to pick multiple choices
	 protected bool $multiple = false;

	 //the human readable label for particular choice
	 public mixed $label {
	 	 get {
	 	 	return $this->choices[$this->value] ?? $this->value;
	 	 }
	 }

	 public function __construct(...$kwargs){
	 	parent::__construct(...$kwargs);
	 }

	 public function choices(array $choices){
	 	 $this->choices = $choices;
	 	 return $this;
	 }
}

