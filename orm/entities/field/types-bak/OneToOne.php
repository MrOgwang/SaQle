<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\Relation;
use SaQle\Orm\Entities\Field\Interfaces\IField;

class OneToOne extends Relation implements IField{
	 public function __construct(...$kwargs){
		 parent::__construct(...$kwargs);
	 }

	  public function get_control_kwargs() : array {
	 	 return array_merge(parent::get_control_kwargs(), [
	 	 	 'type'       => 'select',
	 	 	 'multiple'   =>  false,
	 	 	 'options'    => []
	 	 ]);
	 }
}
