<?php
namespace SaQle\Dao\Field\Types\Base;

use SaQle\Dao\Field\Relations\Interfaces\IRelation;

abstract class Relation extends Simple{
	protected IRelation $relation;
	private bool $isnav;
	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
		$this->isnav = array_key_exists('isnav', $kwargs) ? $kwargs['isnav'] : false;
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
	public function is_navigation(){
		return $this->isnav;
	}
}
?>