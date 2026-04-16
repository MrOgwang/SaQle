<?php
namespace SaQle\Session\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\Response;

class SessionMiddleware extends IMiddleware {

     public function handle(Request $request, ?Response $response = null){
         
         if($request->is_web_request()){
             if(session_status() === PHP_SESSION_NONE){
                 session_start();
             }

             $request->session->activate_session();

             $request->session->set('user', $request->user, true);
         }

         parent::handle($request, $response);
     }
}

