<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\Relation;
use SaQle\Orm\Entities\Field\Interfaces\IField;

class ManyToMany extends Relation implements IField{
	 //the class name for the through model for many to many relations
	 public protected(set) ?string $through = null {
	 	 set(?string $value){
	 	 	 $this->through = $value;
	 	 }

	 	 get => $this->through;
	 }

	 public function __construct(...$kwargs){
		 $kwargs['navigation'] = true; //manytomany fields are navigational
		 parent::__construct(...$kwargs);
	 }

	 protected function get_relation_kwargs() : array{
		 return array_merge(parent::get_relation_kwargs(), ['through']);
	 }

	 //set through model
	 public function through(string $through){
	 	 $this->through = $through;
	 	 return $this;
	 }

	 public function get_control_kwargs() : array{
	 	 return array_merge(parent::get_control_kwargs(), [
	 	 	 'type'       => 'select',
	 	 	 'multiple'   => true,
	 	 	 'options'    => []
	 	 ]);
	 }
}
