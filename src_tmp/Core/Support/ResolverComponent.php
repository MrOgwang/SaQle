<?php

namespace SaQle\Core\Support;

use SaQle\Http\Response\Message;

abstract class ResolverComponent {
	
	 abstract public function get_component() : string;

	 public function get() {
	 	 return Message::ok();
	 }

	 public function post(){
	 	return Message::ok();
	 }
}