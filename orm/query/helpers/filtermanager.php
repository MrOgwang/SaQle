<?php
declare(strict_types = 1);

namespace SaQle\Orm\Query\Helpers;

use SaQle\Orm\Query\Where\WhereBuilder;

trait FilterManager{
	 /**
     * The filter query builder
     * */
 	 public protected(set) WhereBuilder $wbuilder {
         set(WhereBuilder $value){
             $this->wbuilder = $value;
         }

         get => $this->wbuilder;
     }

 	 public function __construct(){
         $this->wbuilder = new WhereBuilder();
     }

 	 public function set_raw_filters(array $filters){
 	 	 $this->before_where("", "");

 	 	 $this->wbuilder->aggregator->filter = $filters;

 	 	 $this->after_where("", "");

	 	 return $this;
 	 }

 	 public function get_raw_filters(){
 	 	 return $this->wbuilder->aggregator->filter;
 	 }

 	 /**
 	  * Get where builder
 	  * @return WhereBuilder
 	  * */
 	 public function get_wbuilder() : WhereBuilder{
 	 	 return $this->wbuilder;
 	 }

 	 public function where(string $field_name, $value){
 	 	 $this->before_where($field_name, $value);

	 	 $this->wbuilder->simple_aggregate([$field_name, $value, 0, "&"]);

	 	 $this->after_where($field_name, $value);

	 	 return $this;
	 }

     /**
      * A literal where: This is a where in which the field_name and the value 
      * are taken literally and not changed. The field_name and the value will be 
      * embedded in the sql statement as is
      * */
	 public function l_where(string $field_name, $value){
	 	 $this->before_where($field_name, $value);

	 	 $this->wbuilder->simple_aggregate([$field_name, $value, 1, "&"]);

	 	 $this->after_where($field_name, $value);

	 	 return $this;
	 }

	 public function or_where(string $field_name, $value){
	 	 $this->before_where($field_name, $value);

	 	 $this->wbuilder->simple_aggregate([$field_name, $value, 0, "|"]);

	 	 $this->after_where($field_name, $value);

	 	 return $this;
	 }

	 public function l_or_where(string $field_name, $value){
	 	 $this->before_where($field_name, $value);

	 	 $this->wbuilder->simple_aggregate([$field_name, $value, 1, "|"]);

	 	 $this->after_where($field_name, $value);

	 	 return $this;
	 }

	 public function gwhere($callback){
	 	 $this->wbuilder->group_aggregate($this, $callback, '&');
	 	 return $this;
	 }

	 public function or_gwhere($callback){
	 	 $this->wbuilder->group_aggregate($this, $callback, '|');
	 	 return $this;
	 }

	 protected function before_where(string $field_name, $value){

	 }

	 protected function after_where(string $field_name, $value){
	 	
	 }
}
