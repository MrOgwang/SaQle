<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The auth middleware injects the session user into the request
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Auth\Middleware;

use SaQle\Http\Response\Message;
use SaQle\Middleware\MiddlewareInterface;
use SaQle\Auth\Services\AuthenticationService;
use SaQle\Core\Services\IService;
use SaQle\Core\Support\ActorContext;

class AuthenticationMiddleware implements MiddlewareInterface {
     
     private IService $auth_service;

     public function __construct(){
         $this->auth_service = resolve(AuthenticationService::class);
     }

     public function handle($request, $response = null) : ?Message {
         
         $user = $this->auth_service->resolve_user();

         if($user){
             $request->session->set('user', $user, true);
             ActorContext::set($user);
         }

         return null;
     }
}
