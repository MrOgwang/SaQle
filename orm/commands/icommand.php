<?php
namespace SaQle\Orm\Commands;

use SaQle\Orm\Operations\IOperation;

abstract class ICommand{
	protected IOperation $_receiver;
	protected $_params;
	public function __construct(IOperation $receiver, ...$params){
		$this->_receiver = $receiver;
		$this->_params   = $params;
	}
	abstract public function execute();
}
?>