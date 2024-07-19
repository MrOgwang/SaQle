<?php
namespace SaQle\Dao\Filter\Interfaces;
abstract class IFilter{
	 protected bool $_is_group;
	 public function __construct($is_group = false){
		 $this->_is_group = $is_group;
	 }
	 public function is_group(){
		 return $this->_is_group;
	 }
}
?>