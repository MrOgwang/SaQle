<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{HttpResponse, HttpMessage};
use SaQle\Http\Response\Types\SseResponse;
use SaQle\Http\Request\Execution\ActionExecutor;

final class SseResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->is_sse_request();
     }

     public function build(Request $request, ?HttpMessage $result = null) : HttpResponse {
         return new SseResponse(fn() => $this->stream($request));
     }

     private function stream(Request $request): void {
         while(true){

             $result = ActionExecutor::execute($request);

             // controller called repeatedly or service polled
             sleep(1);
         }
     }
}
