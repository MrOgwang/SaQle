<?php
namespace SaQle\Http\Methods\Delete;

use SaQle\Http\Response\HttpMessage;

interface IDelete{
	public function delete() : HttpMessage;
}
?>