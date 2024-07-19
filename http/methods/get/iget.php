<?php
namespace SaQle\Http\Methods\Get;

use SaQle\Http\Response\HttpMessage;

interface IGet{
	public function get() : HttpMessage;
}
?>