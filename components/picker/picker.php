<?php
namespace SaQle\Components\Picker;

use SaQle\Http\Response\Message;

class Picker {
	 
	 public function get(array $__props) : Message {

		 return Message::ok([
		 	 'label' => $__props['label'],
		 	 'options' => $__props['options']
		 ]);
		 
	 } 

}