<?php

/**
 * Authentication and authorization configurations
 * */

use SaQle\Auth\Models\BaseUser;
use SaQle\Auth\Services\AuthenticationService;
use SaQle\Auth\Identity\User\Providers\DefaultUserProvider;

return [

	 /**
	  * --------------------------------------------
	  * LOG IN STRATEGIES
	  * -------------------------------------------
	  * 
	  * These are all the ways you would like 
	  * the users of your application to log into your app
	  * 
	  * */
	 'strategies' => [
	 	 'default' => 'password',
	 	 'all'     => ['password', 'google', 'link']
	 ],

     /**
      * --------------------------------------------------
      * USER PROVIDER
      * -------------------------------------------------
      * 
      * The user provider takes in a User ID and returns
      * an instance of a user object to be injected into
      * your request as the session user.
      * 
      * This allows you to define how the session user is
      * to be represented in your application
      * 
      * */
	 'user_provider' => DefaultUserProvider::class,

     /**
      * -------------------------------------------------
      * AUTH ROUTE
      * -------------------------------------------------
      * 
      * The name of the route responsible for signing in
      * users into your application
      * 
      * */
	 'route' => null,

	 /**
	  * ------------------------------------------------
	  * MODEL CLASS
	  * ------------------------------------------------
	  * 
	  * This is the model that represents a user object
	  * in your application. 
	  * 
	  * This class must implement a UserInterface
	  * 
	  * */
	 'model_class' => BaseUser::class,

     /**
      * ------------------------------------------------------
      * 
      * ------------------------------------------------------
      * */
	 //This is the service class that is responsible for authentication.
 	 'backend_class' => AuthenticationService::class,
 	 
 	 'passwords' => [
 	 	 'default' => [
 	 	 	 'algorithm' => PASSWORD_ARGON2ID,
		     'options' => [
		         'memory_cost' => 65536,
		         'time_cost' => 4,
		         'threads' => 2,
		     ],
 	 	 ]
 	 ],

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