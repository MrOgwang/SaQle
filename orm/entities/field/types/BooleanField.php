<?php

namespace SaQle\Orm\Entities\Field\Types;

class BooleanField extends IntegerField {
	 public function __construct(...$kwargs){
		 $kwargs['unsigned'] = true;
		 $kwargs['max']      = 1;
		 $kwargs['min']      = 0;
	 	 parent::__construct(...$kwargs);
	 }
}

