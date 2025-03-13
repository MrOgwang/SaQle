<?php
namespace SaQle\Orm\Query\Order;

class Order{

	 public private(set) array $fields {
	 	 set(array $value){
	 	 	 $this->fields = $value;
	 	 }

	 	 get => $this->fields;
	 }

	 public private(set) string $direction {
	 	 set(string $value){
	 	 	 $this->direction = $value;
	 	 }

	 	 get => $this->direction;
	 }

	 public function __construct($fields, $direction){
		 $this->fields    = $fields;
		 $this->direction = $direction;
	 }
}
?>