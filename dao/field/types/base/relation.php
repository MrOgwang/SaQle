<?php
namespace SaQle\Dao\Field\Types\Base;

use SaQle\Dao\Field\Relations\Interfaces\IRelation;

abstract class Relation extends Simple{
	protected IRelation $relation;
	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}

	protected function get_relation_properties(){
		return [
			/**
			 * Foreign key dao
			 * */
			'fdao' => 'fdao', 

			/**
			 * Primary key model
			 * */
			'pdao' => 'pdao',

			/**
			 * Primary key name
			 * */
			'pk' => 'pk',

			/**
			 * Foreign key name
			 * */
			'fk' => 'fk',

			/**
			 * Whether this is a navigation field or not
			 * */
			'isnav' => 'isnav',

			/**
			 * Whether to fetch multiple objects or not.
			 * */
			'multiple' => 'multiple'
		];
	}
}
?>