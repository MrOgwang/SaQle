<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\RelationField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class ManyToMany extends RelationField {
	 //the class name for the through model for many to many relations
	 protected ?string $through = null;

	 public function __construct(...$kwargs){
		 $kwargs['navigation'] = true;
		 $kwargs['type'] = ColumnType::CHAR;
		 $kwargs['many'] = true;
		 parent::__construct(...$kwargs);
	 }

	 //set through model
	 public function through(string $through){
	 	 $this->through = $through;
	 	 return $this;
	 }

	 public function get_through(){
	 	 return $this->through;
	 }
}
