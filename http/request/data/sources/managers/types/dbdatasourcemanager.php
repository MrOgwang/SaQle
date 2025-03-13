<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Http\Request\Data\Sources\From;

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
	 	 	 $modelclass = $this->type;
	 	 	 $model      = $this->optional ? 
	 	 	 $modelclass::db()->where($this->from->field ?? $this->from->refkey, $refkey_value)->first_or_default() : 
	 	 	 $modelclass::db()->where($this->from->field ?? $this->from->refkey, $refkey_value)->first();

	 	 	 return $model;
	 	 }

	 	 return $refkey_value;
	 }

}
?>