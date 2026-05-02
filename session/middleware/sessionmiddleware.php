<?php
namespace SaQle\Session\Middleware;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Http\Response\HttpMessage;

class SessionMiddleware implements MiddlewareInterface {

     public function handle($request, $response = null) : ?HttpMessage {
         
         if($request->is_web_request()){
             if(session_status() === PHP_SESSION_NONE){
                 session_start();
             }

             $request->session->activate_session();

             if($request->user){
                 $request->session->set('user', $request->user, true);
             }
         }

         return null;
         
     }
}

