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
use SaQle\Middleware\RequestMiddleware;
use SaQle\Auth\Services\AuthenticationService;
use SaQle\Core\Services\IService;
use SaQle\Core\Support\ActorContext;

class AuthenticationMiddleware implements RequestMiddleware {
    
     public function __construct(
         private AuthenticationService $auth_service 
     ){}

     public function before($request) : ?Message {
         
         $user = $this->auth_service->resolve_user();

         if($user){
             $request->session->set('user', $user, true);
             ActorContext::set_actor($user);
         }

         return null;
     }
}
