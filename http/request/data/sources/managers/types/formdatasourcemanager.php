<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Http\Request\Data\Sources\From;
use SaQle\Orm\Entities\Model\Schema\Model;

class FormDataSourceManager extends DataSourceManager{
	 public function __construct(From $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 public function get_value() : mixed {
	 	 $this->is_valid();

	 	 $paramtype = $this->type;
	 	 $refkey    = $this->from->refkey ?? $this->name;

         /**
          * This is a simple type, i.e int, string, float etc
          * 
          * Extract the value and if the type property of the from object is given, run validation based on this,
          * otherwise return the simple value.
          * */
 	 	 if(!is_a($paramtype, Model::class, true)){
 	 	 	 return $this->optional ? $this->request->data->get($refkey) : $this->request->data->get_or_fail($refkey);
 	 	 }

 	 	 /**
 	 	  * This is a model type:
 	 	  * 
 	 	  * 1. if embedded is set to true, the whole model will simply be extracted from a key whose name is pointed by the ref key and json decoded into
 	 	  * an associative array. This array will be used to instantiate the model object and returned.
 	 	  * 
 	 	  * 2. If not embedded, the field values will be hustled from the incoming data and used to construct the model, which will be returned.
 	 	  * 
 	 	  * For all the above two scenarios, if fields array is set, only the fields specified will be extracted
 	 	  * Also the model will run its own validation on instantiation
 	 	  * */

 	 	 //get all the defined fields for this model.
 	 	 $model_fields          = $paramtype::state()->meta->defined_field_names;
 	 	 $model_columns         = $paramtype::state()->meta->column_names;
 	 	 $flipped_model_columns = array_flip($model_columns);

         //the model is embedded in form data
 	 	 if($this->from->embedded){
 	 	 	 $object = $this->optional ? $this->request->data->get($refkey) : $this->request->data->get_or_fail($refkey);
 	 	 	 $object = (array)json_decode($object);
 	 	 	 //if fields is set, strip the object array of non listed fields
 	 	 	 if($this->from->fields){
 	 	 	 	 $object = array_intersect_key($object, array_flip($this->from->fields));
 	 	 	 }
 	 	 	 return new $paramtype(...$object);
 	 	 }

 	 	 //hustle the object values from the form data
 	 	 $fields_to_take = $this->from->fields ? $this->from->fields : $model_fields;
 	 	 $object       = [];
 	 	 foreach($fields_to_take as $f){
 	 	 	 $field_name  = '';
 	 	 	 $column_name = '';
 	 	 	 if(array_key_exists($f, $model_columns)){
 	 	 	 	 $field_name  = $f;
 	 	 	     $column_name = $model_columns[$f];
 	 	 	 }elseif(array_key_exists($f, $flipped_model_columns)){
 	 	 	 	 $field_name  = $flipped_model_columns[$f];
 	 	 	     $column_name = $f;
 	 	 	 }

 	 	 	 if($field_name && $column_name){
 	 	 	     $pv = $this->request->data->get($field_name, $this->request->data->get($column_name));
	 	 	 	 if($pv){
	 	 	 	 	 $object[$f] = $pv;
	 	 	 	 }
 	 	 	 }
 	 	 }

 	 	 return new $paramtype(...$object);
	 }
}
?>