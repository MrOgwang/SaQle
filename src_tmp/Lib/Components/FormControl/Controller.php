<?php

namespace SaQle\Lib\Components\FormControl;

use SaQle\Http\Response\Message;
use SaQle\Core\Ui\Forms\Form;

class Controller {
	 
	 public function get(array $__props) : Message {

	 	 $type = $__props['field']->type;

		 return Message::ok([]);
	 } 

}