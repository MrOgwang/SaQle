<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class BooleanField extends IntegerField {

	 protected function initialize_defaults(){
	 	 $this->unsigned = true;
		 $this->max = 1;
		 $this->min = 0;
		 $this->type = ColumnType::BOOLEAN;

		 parent::initialize_defaults();
     }
}

