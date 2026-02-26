<?php

/**
 * Authentication and authorization configurations
 * */

use SaQle\Auth\Models\BaseUser;
use SaQle\Auth\Services\AuthenticationService;

return [
	 //This is the model that represents a user
	 'model_class' => BaseUser::class,

	 //This is the service class that is responsible for authentication.
 	 'backend_class' => AuthenticationService::class,
     
     //the jwt token key
 	 'jwt_key' => '',

 	 /**
 	  * When a jwt token is issued, this is the number of minutes it is to remain valid.
 	  * Defaults to 5 minutes
 	  * */
 	 'jwt_ttl' => 5,

 	 //the jwt issuer
 	 'jwt_iss' => ''
 ]
?>