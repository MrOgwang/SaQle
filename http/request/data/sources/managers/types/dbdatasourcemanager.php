<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Http\Request\Data\Sources\From;
use SaQle\Orm\Entities\Model\Schema\Model;

class DbDataSourceManager extends DataSourceManager{

	 public function __construct(From $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 private function get_refkey_val(){
	 	 return match($this->from->source){
	 	 	 'path' => $this->optional ? $this->request->route->params->get($this->from->refkey) : $this->request->route->params->get_or_fail($this->from->refkey),
	 	 	 'form', 'body' => $this->optional ? $this->request->data->get($this->from->refkey) : $this->request->data->get_or_fail($this->from->refkey),
	 	 	 'query' => $this->optional ? $this->request->route->queries->get($this->from->refkey) : $this->request->route->queries->get_or_fail($this->from->refkey),
	 	 	 'header' => $this->optional ? $this->request->headers->get($this->from->refkey) : $this->request->headers->get_or_fail($this->from->refkey),
	 	 	 'cookie' => $this->optional ? $this->request->cookies->get($this->from->refkey) : $this->request->cookies->get_or_fail($this->from->refkey)
	 	 };
	 }

	 public function get_value() : mixed {
	 	 $this->is_valid();
	 	 $refkey_value = $this->get_refkey_val();

	 	 if($refkey_value){
	 	 	 $modelclass = $this->type === 'array' ? $this->from->model : $this->type;

	 	 	 if(!is_a($modelclass, Model::class, true)){
 	 	 	     throw new \Exception('The parameter '.$this->name.' must be a valid existing model class or an array. If it is an array you must specify the model in the FromDb attribute! '.$this->type.' was found instead');
 	 	     }

             $column_name = $this->from->field ?? $this->from->refkey;
 	 	     if($this->type === 'array'){
 	 	     	 return $modelclass::get()->where($column_name.'__in', is_array($refkey_value) ? $refkey_value : [$refkey_value])->all();
 	 	     }else{
 	 	     	 return $this->optional ? 
	 	 	     $modelclass::get()->where($column_name, $refkey_value)->first_or_default() : 
	 	 	     $modelclass::get()->where($column_name, $refkey_value)->first();
 	 	     }
	 	 }

	 	 return null;
	 }

}
