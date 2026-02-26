<?php

namespace SaQle\Session;

use SessionHandlerInterface;
use SaQle\Session\Models\Session;

class SessionHandler implements SessionHandlerInterface{
	 public function open($save_path, $session_id) : bool{
		 return true;
	 }
     public function close() : bool{
		 return true;
	 }
     public function destroy($session_id) : bool{
		 return Session::delete(true)->where('session_id__eq', $session_id)->now();
     }
     public function gc($maxlifetime) : int{
		 return true;
     }
     public function read($session_id) : string{
		 $data = Session::get()->where('session_id__eq', $session_id)->first_or_null();
		 return $data ? $data->session_data : "";
     }
     public function write($session_id, $session_data) : bool{
		 $data = Session::get()->where('session_id__eq', $session_id)->first_or_null();
		 if(!$data){
		 	 Session::create(['session_id' => $session_id, 'session_data' => $session_data])->now();
		 }else{
		 	 Session::update(['session_data' => $session_data])->where('session_id__eq', $session_id)->now();
		 }
		 return true;
     }
}
