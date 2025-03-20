<?php
namespace SaQle\Orm\Operations;

use SaQle\Orm\Connection\Interfaces\IConnection;

abstract class IOperation{

	 protected $settings;

	 public function __construct(...$settings){
	 	 $this->settings = $settings;
	 }
}
?>