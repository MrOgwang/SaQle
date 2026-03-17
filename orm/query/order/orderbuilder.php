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

	        foreach($this->order->fields as $i => $field){
	            $direction = $this->order->direction[$i] ?? 'ASC';

	            $order_fields[] = "($field IS NULL)";
	            $order_fields[] = "$field $direction";
	        }

	        $order_clause = " ORDER BY ".implode(", ", $order_fields);
	     }

	     return $order_clause;
	 }
}
