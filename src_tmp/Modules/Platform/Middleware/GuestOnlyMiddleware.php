<?php

namespace SaQle\Modules\Platform\Middleware;

use SaQle\Http\Message\Message;
use SaQle\Middleware\RequestMiddleware;
use SaQle\Auth\Guards\Guard;

class GuestOnlyMiddleware implements RequestMiddleware {

     public function before($request) : Message {

         if(Guard::check('__authenticated__', $request->user)){

             if(!str_starts_with($request->uri(), "/saqle/resources/overview")){
                 return Message::redirect(route('saqle.overview'));
             }
         }

         return Message::ok();
     }

}
?>