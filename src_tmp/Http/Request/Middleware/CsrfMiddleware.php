<?php
namespace SaQle\Http\Request\Middleware;

use SaQle\Middleware\RequestMiddleware;
use SaQle\Http\Message\Message;
use SaQle\Security\Csrf\CsrfToken;

class CsrfMiddleware implements RequestMiddleware {
     
     public function before($request) : Message {

         CsrfToken::get();

         //skip CSRF check for safe HTTP methods
         if($request->is_safe()){
             return Message::ok();
         }

         $token = $request->data->get(CsrfToken::get_key(), $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''); 

         if(!CsrfToken::validate($token)){
             throw authorization_exception('CSRF token validation failed');
         }

         return Message::ok();
     }
}
