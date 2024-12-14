<?php

namespace SaQle\Session;

use SessionHandlerInterface;
use SaQle\Session\Models\Session;

abstract class SessionHandler implements SessionHandlerInterface{
	 public function open($save_path, $session_id) : bool{
		 return true;
	 }
     public function close() : bool{
		 return true;
	 }
     public function destroy($session_id) : bool{
		 return Session::db()->where('session_id__eq', $session_id)->delete(permanently: true);
     }
     public function gc($maxlifetime) : int{
		 return true;
     }
     public function read($session_id) : string{
		 $data = Session::db()->where('session_id__eq', $session_id)->first_or_default();
		 return $data ? $data->session_data : "";
     }
     public function write($session_id, $session_data) : bool{
		 $data = Session::db()->where('session_id__eq', $session_id)->first_or_default();
		 if(!$data){
		 	 Session::db()->add(['session_id' => $session_id, 'session_data' => $session_data])->save();
		 }else{
		 	 Session::db()->where('session_id__eq', $session_id)->set(['session_data' => $session_data])->update();
		 }
		 return true;
     }
}
?>