<?php
namespace SaQle\Lib\Components\Picker;

use SaQle\Http\Response\Message;

class Controller {
	 
	 public function get(array $__props) : Message {

		 return Message::ok([
		 	 'label' => $__props['label'],
		 	 'options' => $__props['options']
		 ]);
		 
	 } 

}