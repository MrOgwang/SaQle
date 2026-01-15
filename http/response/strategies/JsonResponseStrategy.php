<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{HttpResponse, HttpMessage};
use SaQle\Http\Response\Types\JsonResponse;

final class JsonResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->is_api_request() || $request->is_ajax_request();
     }

     public function build(Request $request, HttpMessage $result): HttpResponse {
         return new JsonResponse(
             $result->data, 
             $result->code, 
             $result->message ? $result->message : $result->status_message 
         );
     }
}
