<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class CharField extends TextualField {

	 protected function initialize_defaults(){
	 	 if(!$this->max_length){
	 	 	 $this->max_length = 100;
	 	 }

		 $this->type = ColumnType::CHAR;

		 parent::initialize_defaults();
     }
}

