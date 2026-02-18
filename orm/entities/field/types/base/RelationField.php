<?php

namespace SaQle\Orm\Entities\Field\Types\Base;

use SaQle\Orm\Entities\Field\Attributes\FieldDefinition;
use SaQle\Orm\Entities\Field\Types\{OneToOne, OneToMany, ManyToMany};
use SaQle\Orm\Database\ColumnType;

class RelationField extends Field {

	 //the class name of the foreign key model
	 protected ?string $related_model = null;

     //the class name of the primary model
     protected ?string $local_model = null;

     //the name of the local key
     protected ?string $local_key = null;

     //the name of the foreign key
     protected ?string $foreign_key = null;

     //whether this is a navigation field
     protected bool $navigation = false;

	 //whether mutliple or not
	 protected bool $many = false;

	 //whether to eager feth related field or not
	 protected bool $eager = false;

	 //the field to assign results
	 protected string $field;

	 public function related_model(string $model_class){
	 	 $this->related_model = $model_class;
	 	 return $this;
	 }

	 public function get_related_model(){
	 	 return $this->related_model;
	 }

	 public function local_model(string $model_class){
	 	 $this->local_model = $model_class;
	 	 return $this;
	 }

	 public function get_local_model(){
	 	 return $this->local_model;
	 }

	 public function local_key(string $key){
	 	 $this->local_key = $key;
	 	 return $this;
	 }

	 public function get_local_key(){
	 	 return $this->local_key;
	 }

	 public function foreign_key(string $key){
	 	 $this->foreign_key = $key;
	 	 return $this;
	 }

	 public function get_foreign_key(){
	 	 return $this->foreign_key;
	 }

	 public function navigation(bool $navigation = true){
	 	 $this->navigation = $navigation;
	 	 return $this;
	 }

	 public function is_navigation() : bool {
	 	 return $this->navigation;
	 }

	 public function many(bool $many = true){
	 	 $this->many = $many;
	 	 return $this;
	 }

	 public function is_many() : bool {
	 	 return $this->many;
	 }

	 public function eager(bool $eager = true){
	 	 $this->eager = $eager;
	 	 return $this;
	 }

	 public function is_eager() : bool {
	 	 return $this->eager;
	 }

	 public function field(string $field){
	 	 $this->field;
	 }

	 public function get_field(){
	 	return $this->field;
	 }

	 protected function validate_field_state(){
	 	 if(!$this->related_model){
	 	 	 $this->errors[] = "The related model is required!";
	 	 }

     	 parent::validate_field_state();
	 }

	 protected function initialize_defaults(){

	 	 $this->local_model = $this->model_class;
	 	 $this->field = $this->name;
	 	 $this->type = ColumnType::CHAR;

	 	 if(!$this->foreign_key){
	 	 	 $this->foreign_key = $this->infer_foreign_key();
	 	 }
	 	
	 	 if($this instanceof OneToOne){
	 	 	 if(!$this->column){
	 	 	 	 $this->column = $this->name."_id";
	 	 	 }

             //the local key defaults to the column name
	 	 	 if(!$this->local_key){
	 	 	 	 $this->local_key = $this->column;
	 	 	 }

	 	 	 $this->many = false;
	 	 }elseif($this instanceof ManyToMany || $this instanceof OneToMany){

	 	 	 $this->navigation = true;
	 	 	 $this->many = true;
	 	 	 $this->column = "";

	 	 	 //the local key defaults to the pk name
	 	 	 if(!$this->local_key){
	 	 	 	 $this->local_key = $this->model_pk;
	 	 	 }
	 	 }

	 	 parent::initialize_defaults();
     }

     protected function infer_foreign_key(){
	 	 return $this->related_model::get_pk_name();
	 }
}

