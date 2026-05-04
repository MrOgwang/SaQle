<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{
     Response, 
     HttpMessage
};
use SaQle\Http\Response\Types\JsonResponse;

final class JsonResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->expects_json();
     }

     public function build(Request $request, HttpMessage $result) : Response {

         return new JsonResponse(
             $result->data, 
             $result->code, 
             $result->message ? $result->message : $result->status_message 
         );
     }
}
