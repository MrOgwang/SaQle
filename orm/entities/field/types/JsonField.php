<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\TextualField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\{
      FieldDefinition, 
      ShouldValidate,
      FormControl
};

class JsonField extends TextualField {

     #[ShouldValidate()]
     protected bool $json = true;

	 protected function initialize_defaults(){

         $this->type = ColumnType::JSON;

         if(!$this->control_type){
             $this->control_type = "textarea";
         }

         parent::initialize_defaults();

     }
}

