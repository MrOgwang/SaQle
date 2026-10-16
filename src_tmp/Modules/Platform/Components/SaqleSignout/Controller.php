<?php

namespace SaQle\Modules\Platform\Components\SaqleSignout;

use SaQle\Auth\Services\AuthenticationService;
use SaQle\Http\Message\Message;

class Controller {

	 private $auth_service;
    
     public function __construct(){
         $this->auth_service = resolve(AuthenticationService::class);
     }

	 public function signout(){

	 	 $this->auth_service->logout();
	 	 
	 	 return Message::redirect(route('saqle.login.form'));
	 }
}
?>