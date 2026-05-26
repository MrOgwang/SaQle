<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\{
	 FieldDefinition,
	 FormControl
};

class BooleanField extends IntegerField {

     #[FormControl('choices')]
	 protected ?array $raw_choices = [
	 	 0 => 'False', 
	 	 1 => 'True'
	 ];

	 protected function initialize_defaults(){
	 	 $this->unsigned = true;
		 $this->max = 1;
		 $this->min = 0;
		 $this->type = ColumnType::BOOLEAN;

		 if(!$this->control_type){
	 	 	 $this->control_type = "radio";
	 	 }

		 parent::initialize_defaults();
     }
}

