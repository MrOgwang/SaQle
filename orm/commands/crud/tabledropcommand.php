<?php
namespace SaQle\Orm\Commands\Crud;

use SaQle\Orm\Commands\ICommand;

class TableDropCommand extends ICommand{
	 public function execute(){
		 $this->_receiver->settings($this->_params);
	 	 return $this->_receiver->drop();
	 }
}
?>