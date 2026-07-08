<?php
namespace SaQle\Components\Error;

use SaQle\Http\Response\Message;
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Core\Support\ErrorComponent;

class Error implements ErrorComponent {

	 public function get(
	 	 int $code = FeedBack::INTERNAL_SERVER_ERROR, 
	 	 string $message = "Internal Server Error", 
	 	 mixed $data = null
	 ) : Message {
		 return Message::ok([
		 	 'code'    => $code,
		 	 'message' => $message,
		 	 'data'    => $data
		 ]);
	 }

}