<?php
namespace SaQle\Dao\Field\Types\Base;

use SaQle\Dao\Field\Relations\Interfaces\IRelation;
use SaQle\Dao\Field\Relations\{One2One, One2Many, Many2Many};
use SaQle\Dao\Field\Types\{OneToOne, OneToMany, ManyToMany};
use SaQle\Dao\Field\FormControlTypes;

abstract class Relation extends Simple{
	protected IRelation $relation;
	private bool $isnav;
	public function __construct(...$kwargs){
		 if(PRIMARY_KEY_TYPE === "GUID"){
			/**
			 * Fill in the data types.
			 * */
			$kwargs['dtype'] = "VARCHAR";
			$kwargs['vtype'] = "text";
			$kwargs['ctype'] = FormControlTypes::TEXT->value;
			$kwargs['ptype'] = "string";

			/**
			 * Fill in the validation props
			 * */
			$kwargs['length'] = 255;
			$kwargs['max'] = 255;
		 }else{
			 /**
			 * Fill in the data types.
			 * */
			$kwargs['dtype'] = "INT";
			$kwargs['vtype'] = "number";
			$kwargs['ctype'] = isset($kwargs['ctype']) ? $kwargs['ctype'] : FormControlTypes::NUMBER->value;
			$kwargs['ptype'] = "int";

			/**
			 * Fill in the validation props
			 * */
			$kwargs['length']   = 11;
			$kwargs['absolute'] = true;
			$kwargs['zero']     = false;
			$kwargs['max']      = 4294967295;
			$kwargs['min']      = 1;
		 }
		 parent::__construct(...$kwargs);
		 $this->isnav = array_key_exists('isnav', $this->kwargs) ? $this->kwargs['isnav'] : false;
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
			'multiple' => 'multiple',

			/**
			 * The name of the field to assign results on fetch.
			 * */
			'field' => 'field',

			/**
			 * Whether to eager load field or not.
			 * */
			'eager' => 'eager'
		];
	}

	public function is_navigation(){
		return $this->isnav;
	}

	public function initialize(){
		 parent::initialize();
		 /**
		  * Create a relation object.
		  * */
		 $this->create_relation_object();
	}

	public function create_relation_object(){
		 if(!isset($this->kwargs['fdao'])){
		 	throw new \Exception("Please provide the foreign key model using fdao parameter!");
		 }
		 
		 $pdao = $this->get_model_class();
		 $fdao = $this->kwargs['fdao']; //throw an exception here if fdao doesn't exist.
		 $field = $this->kwargs['field'] ?? $this->property_name;
		 $pk = $this->kwargs['pk'] ?? $this->model_class_pk; //if pk is not provided, should default to the pk name of current model.
		 $fk = $this->kwargs['fk'] ?? $this->model_class_pk; //if fk is not provided, should default to the pk name of current model.
		 $isnav = $this->kwargs['isnav'] ?? false;
		 $multiple = $this->kwargs['multiple'] ?? false;
		 $eager = $this->kwargs['eager'] ?? false;

		 if($this instanceof OneToOne){
		 	$this->relation = new One2One($pdao, $fdao, $field, $pk, $fk, $isnav, $multiple, $eager);
		 }elseif($this instanceof OneToMany){
		 	$this->relation = new One2Many($pdao, $fdao, $field, $pk, $fk, $isnav, $multiple, $eager);
		 }elseif($this instanceof ManyToMany){
		 	$this->relation = new Many2Many($pdao, $fdao, $field, $pk, $fk, $isnav, $multiple, $eager);
		 }
	}

	public function is_eager(){
		return $this->relation->get_eager();
	}

	public function get_relation(){
		return $this->relation;
	}
}
?>