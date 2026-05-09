<?php
namespace SaQle\Components\Error;

use SaQle\Http\Response\Message;

class Error {

	 public function get(){
		 return Message::ok();
	 }

}
?>