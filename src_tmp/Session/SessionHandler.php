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
		 return Session::using(system_connection())->delete(true)->where('session_id__eq', $session_id)->now();
     }

     public function gc($maxlifetime) : int{
		 return true;
     }

     public function read($session_id) : string{

		 $data = Session::using(system_connection())->get()->where('session_id__eq', $session_id)->first_or_null();

		 return $data ? $data->session_data : "";
     }

     public function write($session_id, $session_data) : bool{

		 $data = Session::using(system_connection())->get()->where('session_id__eq', $session_id)->first_or_null();

		 if(!$data){
		 	 Session::using(system_connection())
		 	 ->create(['session_id' => $session_id, 'session_data' => $session_data])->now();
		 }else{
		 	 Session::using(system_connection())
		 	 ->update(['session_data' => $session_data])->where('session_id__eq', $session_id)->now();
		 }

		 return true;
     }
}
