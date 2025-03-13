<?php
namespace SaQle\Orm\Commands\Crud;

use SaQle\Orm\Commands\ICommand;

class RunCommand extends ICommand{
	 public function execute(){
		 $this->_receiver->settings($this->_params);
	 	 return $this->_receiver->run();
	 }
}
?>