<?php

namespace SaQle\Components\SaqleSignin;

use SaQle\Auth\Services\AuthenticationService;
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

		 if(!$auth_result->success){
		 	 throw authorization_exception("Invalid credentials!");
		 }

		 return Message::redirect(route('saqle.admin.dashboard'));
	 }

	 public function get(){
		 return Message::ok([]);
	 }

	 public function signout(){

	 	 $this->auth_service->logout();
	 	 
	 	 return Message::redirect(route('saqle.login.form'));
	 }
}
?>