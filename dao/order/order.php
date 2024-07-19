<?php
namespace SaQle\Dao\Order;

class Order implements IOrder{
	 private array  $_fields;
	 private string $_direction;
	 public function __construct($fields, $direction){
		 $this->_fields    = $fields;
		 $this->_direction = $direction;
	 }

	 public function get_fields(){
	 	return $this->_fields;
	 }
	 public function get_direction(){
	 	return $this->_direction;
	 }
}
?>