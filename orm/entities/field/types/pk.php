<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\Scalar;
use SaQle\Orm\Entities\Field\Interfaces\IField;

class Pk extends Scalar implements IField{
	 public function __construct(...$kwargs){
		 if(PRIMARY_KEY_TYPE === "GUID"){
			 $kwargs['column_type']     = "VARCHAR";
			 $kwargs['validation_type'] = "text";
			 $kwargs['primitive_type']  = "string";
			 $kwargs['length']          = 255;
			 $kwargs['maximum']         = 255;
		 }else{
			 $kwargs['column_type']     = "INT";
			 $kwargs['validation_type'] = "number";
			 $kwargs['primitive_type']  = "int";
			 $kwargs['length']          = 11;
			 $kwargs['maximum']         = 4294967295;
			 $kwargs['absolute']        = true;
			 $kwargs['zero']            = false;
			 $kwargs['minimum']         = 1;
		 }
		 $kwargs['required']            = true;
		 parent::__construct(...$kwargs);
	 }
}
