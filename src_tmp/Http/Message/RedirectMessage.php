<?php

namespace SaQle\Http\Message;

use SaQle\Http\HttpStatus;

class RedirectMessage extends Message {

     private bool $_keep_method = false;

     private ?array $notification = null;

	 public function __construct(string $url){
	 	 parent::__construct(HttpStatus::FOUND);

	 	 $this->with('url', $url);
	 }

	 public function get_url(): string {
         return $this->data['url'];
     }

	 public function permanently() : static {
	 	 $this->code = $this->_keep_method ? HttpStatus::PERMANENT_REDIRECT : HttpStatus::MOVED_PERMANENTLY;
	 	 
	 	 return $this;
	 }

	 public function temporarily() : static {
	 	 $this->code = $this->_keep_method ? HttpStatus::TEMPORARY_REDIRECT : HttpStatus::FOUND;
	 	 
	 	 return $this;
	 }

	 public function keep_method() : static {
	 	 $this->_keep_method = true;
	 	 
	 	 return $this;
	 }

	 public function as_get() : static {
	 	 $this->code = HttpStatus::SEE_OTHER;

	 	 return $this;
	 }

	 public function notify(string $type, string $message) : static {

	 	 $this->notification = [
	 	 	 'type'    => $type,
	 	 	 'message' => $message
	 	 ];

	 	 return $this;

	 }

	 public function get_notification() : ?array {
	 	 return $this->notification;
	 }

}