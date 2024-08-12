<?php
namespace SaQle\Dao\Commands\Crud;

use SaQle\Dao\Commands\ICommand;

class InsertCommand extends ICommand{
	 public function execute(){
	 	 try{
	 	 	 $this->_receiver->settings($this->_params);
	 	     return $this->_receiver->insert();
	 	 }catch(\Exception $ex){
	 	 	 throw $ex;
	 	 }
	 }
}
?>