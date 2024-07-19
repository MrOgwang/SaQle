<?php
namespace SaQle\Dao\Order\Manager;

use SaQle\Dao\Order\IOrder;
use SaQle\Dao\Order\Order;

class OrderManager implements IOrderManager{
	 protected ?IOrder $_order = null;

	 /*setters*/
	 public function set_order(array $fields, string $direction = "ASC"){
	 	$this->_order = new Order(fields: $fields, direction: $direction);
	 }
	 
	 /*getters*/
	 public function get_order(){
	 	return $this->_order;
	 }

	 public function construct_order_clause(){
		 $order_clause = "";
		 if(!is_null($this->_order)){
			 $order_fields = [];
			 foreach($this->_order->get_fields() as $field){
			 	 $order_fields[] = $field;
			 	//array_push($order_fields, $order['database'].".".$order['table'].".".$field);
			 }
			 $order_clause = " ORDER BY ".implode(", ", $order_fields)." ".$this->_order->get_direction();
		 }
		 return $order_clause;
	 }
}
?>