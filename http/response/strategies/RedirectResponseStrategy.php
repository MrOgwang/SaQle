<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{
     Response, 
     Message
};
use SaQle\Http\Response\Types\RedirectResponse;

final class RedirectResponseStrategy implements ResponseStrategy {
    
     public function build(Request $request, Message $result) : Response {
         return new RedirectResponse((string)$result->data);
     }
}