<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{HttpResponse, HttpMessage};
use SaQle\Http\Response\Types\RedirectResponse;
use SaQle\Core\Components\{
     ComponentTreeBuilder, 
     ComponentRenderer, 
     ComponentContext
};

final class RedirectResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->is_web_request();
     }

     public function build(Request $request, ?HttpMessage $result = null) : HttpResponse {
         return new RedirectResponse($result->get_redirect());
     }
}