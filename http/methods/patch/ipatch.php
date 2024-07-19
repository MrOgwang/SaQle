<?php
namespace SaQle\Http\Methods\Patch;

use SaQle\Http\Response\HttpMessage;

interface IPatch{
	public function patch() : HttpMessage;
}
?>