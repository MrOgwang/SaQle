<?php
namespace SaQle\Session\Middleware;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Http\Response\Message;
use SaQle\Core\Support\Session;

class SessionMiddleware implements MiddlewareInterface {

     public function handle($request, $response = null) : ?Message {
        
         if(!$response && $request->is_web_request()){

             if(session_status() === PHP_SESSION_NONE){
                 session_start();
             }

             $request->session->activate_session();

             if($request->user){
                 $request->session->set('user', $request->user, true);
             }

             if(Session::has('__flash_next')){
                 Session::set('__flash_current', Session::get('__flash_next'));
                 Session::remove('__flash_next');
             }

             $request->session->set('__auth_context', auth_context(), true);
         }

         if($response){
             Session::remove('__flash_current');
         }

         return null;
     }
}

