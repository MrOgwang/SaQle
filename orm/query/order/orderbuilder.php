<?php
namespace SaQle\Orm\Query\Order;

class OrderBuilder{

	 public ?Order $order = null {
	 	 set(?Order $value){
	 	 	 $this->order = $value;
	 	 }

	 	 get => $this->order;
	 }

	 public function construct_order_clause(){
		 $order_clause = "";
		 if(!is_null($this->order)){
			 $order_fields = [];
			 foreach($this->order->fields as $field){
			 	 $order_fields[] = $field;
			 }
			 $order_clause = " ORDER BY ".implode(", ", $order_fields)." ".$this->order->direction;
		 }
		 return $order_clause;
	 }
}
?>