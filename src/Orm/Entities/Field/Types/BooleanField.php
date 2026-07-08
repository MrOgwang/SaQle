<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\{
	 FieldDefinition,
	 FormControl
};
use RuntimeException;

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

     public function default(mixed $default){
     	 if(!is_bool($default) && !is_int($default)){
     	 	 throw new RuntimeException('Invalid boolean value. Please provide an integer or a bool!');
     	 }

     	 if(is_bool($default)){
     	 	 $default_value = $default === true ? 1 : 0;
     	 }elseif(is_int($default)){
     	 	 $default_value = $default > 0 ? 1 : 0;
     	 }

	 	 $this->default = $default_value;
	 	 return $this;
	}
}

