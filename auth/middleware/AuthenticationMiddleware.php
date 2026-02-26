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

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Auth\Services\AuthenticationService;
use SaQle\Core\Services\IService;

class AuthenticationMiddleware extends IMiddleware {
     
     private IService $auth_service;

     public function __construct(){
         $this->auth_service = resolve(AuthenticationService::class);
     }

     public function handle(MiddlewareRequestInterface $request){
         
         $user = $this->auth_service->resolve_user();

         if($user){
             $request->session->set('user', $user, true);
         }
         
         parent::handle($request);
     }
}
