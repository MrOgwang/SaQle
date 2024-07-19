<?php
namespace SaQle\Http\Methods\Post;

use SaQle\Http\Response\HttpMessage;

interface IPost{
	public function post() : HttpMessage;
}
?>