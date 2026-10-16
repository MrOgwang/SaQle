<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\Response;
use SaQle\Http\Message\Message;
use SaQle\Http\Response\Types\JsonResponse;

final class JsonResponseStrategy implements ResponseStrategy {
    
     public function build(Request $request, Message $result) : Response {

         return new JsonResponse($result->get_data(), $result->get_code(), $result->get_message());
     }
}
