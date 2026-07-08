<?php
namespace SaQle\Orm\Query\Order;

class Order{

	 public private(set) array $fields {
	 	 set(array $value){
	 	 	 $this->fields = $value;
	 	 }

	 	 get => $this->fields;
	 }

	 public private(set) array $direction {
	 	 set(array $value){
	 	 	 $this->direction = $value;
	 	 }

	 	 get => $this->direction;
	 }

	 public function __construct(array $fields, array $direction){
		 $this->fields    = $fields;
		 $this->direction = $direction;
	 }
}
