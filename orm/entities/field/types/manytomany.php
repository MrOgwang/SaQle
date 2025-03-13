<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\Relation;
use SaQle\Orm\Entities\Field\Interfaces\IField;

class ManyToMany extends Relation implements IField{
	 //the class name for the through model for many to many relations
	 public string $through = '' {
	 	 set(string $value){
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
}
?>