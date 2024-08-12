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
	protected function get_relation_properties(){
		return array_merge(parent::get_relation_properties(), [
			/**
			 * The through model schema to use for this relation.
			 * */
			'through' => 'through',
		]);
	}
}
?>