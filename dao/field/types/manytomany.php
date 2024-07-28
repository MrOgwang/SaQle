<?php

namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Relation;
use SaQle\Dao\Field\Interfaces\IField;

class ManyToMany extends Relation implements IField{
	public function __construct(...$kwargs){
		/**
		 * ManyToMany fields are navigational by force.
		 * */
		$kwargs['isnav'] = true;
		parent::__construct(...$kwargs);
	}
}
?>