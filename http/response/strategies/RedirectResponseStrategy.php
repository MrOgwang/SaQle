<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{
     Response, 
     Message
};
use SaQle\Http\Response\Types\RedirectResponse;

final class RedirectResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->expects_redirect();
     }

     public function build(Request $request, Message $result) : Response {
         return new RedirectResponse($result->redirect_to());
     }
}