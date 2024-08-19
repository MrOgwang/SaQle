<?php

namespace SaQle\Session;

use SessionHandlerInterface;

use SaQle\Apps\Account\Data\AccountsDbContext;

abstract class SessionHandler implements SessionHandlerInterface{
	 private $context;
	 public function __construct($context){
	 	 $this->context = $context;
	 }
	 public function open($save_path, $session_id) : bool{
		 return true;
	 }
     public function close() : bool{
		 return true;
	 }
     public function destroy($session_id) : bool{
		 return $this->context->sessions->where('session_id__eq', $session_id)->delete(permanently: true);
     }
     public function gc($maxlifetime) : int{
		 return true;
     }
     public function read($session_id) : string{
		 $data = $this->context->sessions->where('session_id__eq', $session_id)->first_or_default();
		 return $data ? $data->session_data : "";
     }
     public function write($session_id, $session_data) : bool{
		 $data = $this->context->sessions->where('session_id__eq', $session_id)->first_or_default();
		 if(!$data){
		 	 $this->context->sessions->add(['session_id' => $session_id, 'session_data' => $session_data])->save();
		 }else{
		 	 $this->context->sessions->where('session_id__eq', $session_id)->set(['session_data' => $session_data])->update();
		 }
		 return true;
     }
}
?>