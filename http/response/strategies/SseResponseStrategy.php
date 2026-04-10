<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{Response, HttpMessage};
use SaQle\Http\Response\Types\SseResponse;
use SaQle\Http\Request\Execution\ActionExecutor;

final class SseResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->expects_sse();
     }

     public function build(Request $request, ?HttpMessage $result = null) : Response {
         return new SseResponse(fn() => $this->stream($request));
     }

     private function stream(Request $request): void {

         $event_id = 0;
         $event = "message";
         $interval = 1;

         if($request->route->sse){
             $event = $request->route->sse['event'];
             $interval = $request->route->sse['interval'];
         }

         while(true){

             if(connection_aborted()){
                 break;
             }

             $result = ActionExecutor::execute($request)->data;

             $event_id++;

             echo "id: {$event_id}\n";
             echo "event: {$event}\n";
             echo "data: " . json_encode($result) . "\n\n";

             ob_flush();
             flush();

             sleep($interval);
         }
     }
}
