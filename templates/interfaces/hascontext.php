<?php
declare(strict_types = 1);
namespace SaQle\Templates\Interfaces;

interface HasContext{
	 /**
	  * This is a key => value array representing data coming from this view's controller.
	  * The data will be replaced by the placeholders defined in this view's template string.
	  * */
	 public function get_context() : array;
}
?>