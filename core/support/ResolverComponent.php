<?php

namespace SaQle\Core\Support;

abstract class ResolverComponent {
	
	 abstract public function get_component() : string;

	 public function get() {
	 	 return ok();
	 }

	 public function post(){
	 	return ok();
	 }
}