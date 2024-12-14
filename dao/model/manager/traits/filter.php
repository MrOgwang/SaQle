<?php
declare(strict_types = 1);

namespace SaQle\Dao\Model\Manager\Traits;

use SaQle\Dao\Filter\Interfaces\IFilterManager;
use SaQle\Services\Container\Cf;

trait Filter{
	 /**
     * The filter manager handles filtering
     * */
 	 private ?IFilterManager $fmanager = null;

 	 public function set_raw_filters(array $filter){
 	 	 $this->get_filter_manager()->set_raw_filter($filter);
	 	 return $this;
 	 }

 	 /**
 	  * Set the filter manager
 	  * */
 	 protected function set_filter_manager(){
 	 	 if(is_null($this->fmanager)){
 	 	 	$this->fmanager = Cf::create(IFilterManager::class);
 	 	 }
 	 }

 	 /**
 	  * Get filter manager
 	  * @return IFilterManager
 	  * */
 	 public function get_filter_manager() : IFilterManager{
 	 	 $this->set_filter_manager();
 	 	 return $this->fmanager;
 	 }

 	 public function where(string $field_name, $value){
	 	 $this->get_filter_manager()->simple_aggregate([$field_name, $value, 0, "&"]);
	 	 return $this;
	 }

     /**
      * A literal where: This is a where in which the field_name and the value 
      * are taken literally and not changed. The field_name and the value will be 
      * embedded in the sql statement as is
      * */
	 public function l_where(string $field_name, $value){
	 	 $this->get_filter_manager()->simple_aggregate([$field_name, $value, 1, "&"]);
	 	 return $this;
	 }

	 public function or_where(string $field_name, $value){
	 	 $this->get_filter_manager()->simple_aggregate([$field_name, $value, 0, "|"]);
	 	 return $this;
	 }

	 public function l_or_where(string $field_name, $value){
	 	 $this->get_filter_manager()->simple_aggregate([$field_name, $value, 1, "|"]);
	 	 return $this;
	 }

	 public function gwhere($callback){
	 	 $this->get_filter_manager()->group_aggregate($this, $callback, '&');
	 	 return $this;
	 }

	 public function or_gwhere($callback){
	 	 $this->get_filter_manager()->group_aggregate($this, $callback, '|');
	 	 return $this;
	 }
}
?>