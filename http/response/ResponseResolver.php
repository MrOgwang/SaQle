<?php

namespace SaQle\Http\Response;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\Strategies\{
     SseResponseStrategy, 
     JsonResponseStrategy, 
     WebResponseStrategy,
     RedirectResponseStrategy
};
use RuntimeException;

final class ResponseResolver {

     public function resolve(Request $request, ?HttpMessage $result = null) : HttpResponse {

         /**
          * The only time the result is not null,
          * is when an exception has happened!
          * */
         $strategies = !$result ? $this->strategies() : $this->exception_strategies();
        
         foreach($strategies as $strategy){
             if($strategy->supports($request)){
                 return $strategy->build($request, $result);
             }
         }

         throw new RuntimeException('No response strategy matched');
     }

     private function strategies(): array {
         return [
             new SseResponseStrategy(),
             new JsonResponseStrategy(),
             new WebResponseStrategy(),
         ];
     }

     private function exception_strategies(): array {
         return [
             new SseResponseStrategy(),
             new JsonResponseStrategy(),
             new RedirectResponseStrategy(),
         ];
     }
}
