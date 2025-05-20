<?php
namespace SaQle\Http\Methods\Interfaces;

use SaQle\Http\Response\HttpMessage;

interface IHttpMethods{
	 public function get()    : HttpMessage;
	 public function post()   : HttpMessage;
	 public function patch()  : HttpMessage;
	 public function delete() : HttpMessage;
}
