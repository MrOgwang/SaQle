<?php

namespace SaQle\Components\SaqleSignin;

use App\Modules\Account\Services\AuthenticationService;
use SaQle\Http\Response\Message;

class SaqleSignin {
	 private $auth_service;
    
     public function __construct(){
         $this->auth_service = resolve(AuthenticationService::class);
     }

	 public function post(
	 	 string $username, 
	 	 string $password,
	 ){
		 $auth_result = $this->auth_service->login('password', ['username' => $username, 'password' => $password]);

		 if(!$auth_result->success || ($auth_result->success && $auth_result->user->is_removed)){
		 	 throw authorization_exception("Invalid credentials!");
		 }
		 
		 //$next = request()->session->get("auth.next", config('app.root_domain'));

		 //return Message::redirect($next)->as_get();
		 return Message::ok();
	 }

	 public function get(){
		 return Message::ok([]);
	 }

	 public function signout(){

	 	 $this->auth_service->logout();
	 	 
	 	 return Message::redirect(config('app.root_domain'))->as_get();
	 }
}
?>