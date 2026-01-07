<?php

namespace SaQle\Http\Response;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\HttpMessage;
use SaQle\Http\Response\Strategies\{SseResponseStrategy, JsonResponseStrategy, WebResponseStrategy};
use RuntimeException;

final class ResponseResolver {

     public function resolve(Request $request, HttpMessage $result): HttpResponse {
         foreach ($this->strategies() as $strategy) {
             if ($strategy->supports($request)) {
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
}
