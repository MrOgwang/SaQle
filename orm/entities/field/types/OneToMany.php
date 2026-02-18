<?php

namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Types\Base\RelationField;
use SaQle\Orm\Database\ColumnType;
use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;

class OneToMany extends RelationField {

	 protected function infer_foreign_key(){
	 	 $related_model_fields = $this->related_model::get_fields();
	 	 foreach($related_model_fields as $f){
	 	 	 if($f instanceof OneToOne && $this->model_class === $f->get_related_model()){
	 	 	 	 return $f->get_name()."_id";
	 	 	 }
	 	 }

	 	 return null;
	 }
}
