<?php
namespace SaQle\Orm\Operations;

abstract class IOperation {

	 protected $settings;

	 public function __construct(...$settings){
	 	 $this->settings = $settings;
	 }
}
