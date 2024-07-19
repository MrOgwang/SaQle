<?php
namespace SaQle\Dao\Model;

class ModelCollection implements IModel{
	 function __construct(private array $models = []){

	 }
	 public function add(string $name, IModel $model){
		 $this->models[$name] = $model;
	 }
	 public function get(string $name){
		 return array_key_exists($name, $this->models) ? $this->models[$name] : null;
	 }
	 public function remove(string $name){
		 
	 }
	 public function get_all(){
		 return $this->models;
	 }
}
?>