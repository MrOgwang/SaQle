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
use RuntimeException;

final class ResponseResolver {

     public function resolve(Request $request, HttpMessage $result) : Response {

         $response_strategies = [
             new SseResponseStrategy(),
             new JsonResponseStrategy(),
             new FileResponseStrategy(),
             new HtmlResponseStrategy(),
             new RedirectResponseStrategy()
         ];

         foreach($response_strategies as $strategy){
             if($strategy->supports($request)){
                 return $strategy->build($request, $result);
             }
         }

         throw new RuntimeException('No response strategy matched');
     }
}
