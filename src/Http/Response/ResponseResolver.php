<?php

namespace SaQle\Http\Response;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\Strategies\{
     SseResponseStrategy, 
     JsonResponseStrategy, 
     HtmlResponseStrategy,
     FileResponseStrategy,
     RedirectResponseStrategy
};
use SaQle\Http\Response\{
     RedirectMessage,
     FileMessage,
     ResponseType
};
use RuntimeException;

final class ResponseResolver {

     public function resolve(Request $request, Message $result) : Response {
        
         $strategy = null;

         if($result instanceof RedirectMessage){
             $strategy = new RedirectResponseStrategy();
         }elseif($result instanceof FileMessage){
             $strategy = new FileResponseStrategy();
         }else{
             $strategy = match($request->responsetype){
                 ResponseType::JSON => new JsonResponseStrategy(),
                 ResponseType::SSE  => new SseResponseStrategy(),
                 ResponseType::HTML => new HtmlResponseStrategy()
             };
         }

         if(!$strategy){
             throw new RuntimeException('No response strategy matched the request!');
         }

         return $strategy->build($request, $result);
     }
}
