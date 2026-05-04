<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{
     Response, 
     HttpMessage
};
use SaQle\Http\Response\Types\SseResponse;

final class SseResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->expects_sse();
     }

     public function build(Request $request, HttpMessage $result) : Response {
         return new SseResponse(fn() => $this->stream($request, $result));
     }

     private function stream(Request $request, HttpMessage $result): void {

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

             $event_id++;

             echo "id: {$event_id}\n";
             echo "event: {$event}\n";
             echo "data: " . json_encode($result->data) . "\n\n";

             ob_flush();
             flush();

             sleep($interval);
         }
     }
}
