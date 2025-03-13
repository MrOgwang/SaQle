<?php
namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Orm\Entities\Field\Relations\{One2One, One2Many, Many2Many};
use SaQle\Orm\Entities\Field\Types\{OneToOne, OneToMany, ManyToMany};
use SaQle\Orm\Entities\Field\Relations\Interfaces\IRelation;
use SaQle\Orm\Entities\Field\Relations\Traits\Relationship;

abstract class Relation extends RealField implements IRelation{
	 use Relationship;
	 
	 public function __construct(...$kwargs){
	 	 $kwargs['column_type']     = PRIMARY_KEY_TYPE === "GUID" ? "VARCHAR" : "INT";
		 $kwargs['validation_type'] = PRIMARY_KEY_TYPE === "GUID" ? "text"    : "number";
		 $kwargs['primitive_type']  = PRIMARY_KEY_TYPE === "GUID" ? "string"  : "int";
		 $kwargs['length']          = PRIMARY_KEY_TYPE === "GUID" ? 255       : 11;
		 $kwargs['maximum']         = PRIMARY_KEY_TYPE === "GUID" ? 255       : 4294967295;

		 if(!PRIMARY_KEY_TYPE === "GUID"){
		 	 $kwargs['absolute'] = true;
			 $kwargs['zero']     = false;
			 $kwargs['minimum']  = 1;
		 }
		 parent::__construct(...$kwargs);
	 }

	 protected function get_relation_kwargs() : array{
		 return [
		 	 'fmodel',
		 	 'pmodel',
		 	 'pk',
		 	 'fk',
			 'navigation',
			 'multiple',
			 'field',
			 'eager'
		 ];
	 }

	 public function get_relation() : IRelation{
		 if(!$this->fmodel){
		 	 throw new \Exception("Please provide the foreign key model using fmodel parameter!");
		 }

		 //Default field to the name of property this field is assined to
		 $this->field = $this->field ?? $this->field_name;

         //Default pk to the name of property this field is assigned to
		 $this->pk    = $this->pk ?? ($this->column_name ?? $this->field_name); 

		 //Default fk to the name of primary key property of the foreign model
		 if(!$this->fk){
		 	 $fmodel      = $this->fmodel;
		     $this->fk    = $fmodel::state()->meta->pk_name;
		 }

		 if($this instanceof OneToOne)
		 	 return new One2One($this->pmodel, $this->fmodel, $this->field, $this->pk, $this->fk, $this->navigation, $this->multiple, $this->eager);

		 if($this instanceof OneToMany)
		 	 return new One2Many($this->pmodel, $this->fmodel, $this->field, $this->pk, $this->fk, $this->navigation, $this->multiple, $this->eager);

		 if($this instanceof ManyToMany)
		 	 return new Many2Many($this->pmodel, $this->fmodel, $this->field, $this->pk, $this->fk, $this->navigation, $this->multiple, $this->eager, $this->through ?? null);
	 }
}
?>