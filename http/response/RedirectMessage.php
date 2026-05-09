<?php

namespace SaQle\Http\Response;

class RedirectMessage extends Message {

     private bool $_keep_method = false;

	 public function __construct(string $url){
	 	 parent::__construct(self::FOUND, $url);
	 }

	 public function permanently(){
	 	 $this->code = $this->_keep_method ? self::PERMANENT_REDIRECT : self::MOVED_PERMANENTLY;
	 	 return $this;
	 }

	 public function temporarily(){
	 	 $this->code = $this->_keep_method ? self::TEMPORARY_REDIRECT : self::FOUND;
	 	 return $this;
	 }

	 public function keep_method(){
	 	 $this->_keep_method = true;
	 	 return $this;
	 }

	 public function as_get(){
	 	 $this->code = self::SEE_OTHER;
	 }

	 public function flash(string $key, mixed $data = null){

	 }

	 public function should_flash() : bool {
	 	return true;
	 }

}