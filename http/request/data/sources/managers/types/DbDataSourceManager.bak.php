<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Core\Support\BindFrom;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Http\Request\Execution\TypeInspector;

class DbDataSourceManager extends DataSourceManager{

	 public function __construct(BindFrom $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 private function get_key_val(string $key){

	 	 $sources = ['params', 'queries', 'data'];

	 	 foreach($sources as $s){
	 	 	 $value = $this->request->$s->get($key);
             if($value !== null)
             	 $value;
         }

         return null;
	 }

	 public function get_value() : mixed {

	 	 $class_name = TypeInspector::get_class_name($this->type);

         if($class_name && !is_subclass_of($class_name, Model::class)){
             throw new Exception("Cannot bind data of type: {$class_name} from the database! Bind from container instead!");
         }

	 	 $key_parts = explode("=>", $this->from->key);
	 	 $key = $key_parts[0];
	 	 $mapto = $key_parts[1] ?? $key;

	 	 $key_value = $this->get_key_val($key);

	 	 if(!$key_value && $this->optional)
	 	 	 return null;



	 	 if($refkey_value){
	 	 	 $modelclass = $this->type === 'array' ? $this->from->model : $this->type;

             $column_name = $this->from->field ?? $this->from->refkey;
 	 	     if($this->type === 'array'){
 	 	     	 return $modelclass::get()->where($column_name.'__in', is_array($refkey_value) ? $refkey_value : [$refkey_value])->all();
 	 	     }else{
 	 	     	 return $this->optional ? 
	 	 	     $modelclass::get()->where($column_name, $refkey_value)->first_or_null() : 
	 	 	     $modelclass::get()->where($column_name, $refkey_value)->first_or_fail();
 	 	     }
	 	 }

	 	 return null;
	 }

}
