<?php

namespace SaQle\Modules\Platform\Components\SaqleSignin;

use SaQle\Auth\Services\{AuthenticationService, PasswordHashService};
use SaQle\Http\Message\Message;

class Controller {
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
		 	 return Message::reload()->notify('error', 'Invalid credentials!');
		 }   

		 return Message::redirect(route(resource_route_name(action: 'overview', is_platform: true)));
	 }

	 public function get(){ 
	 	 return Message::ok();
	 }
}
?>