<?php

namespace SaQle\Http\Cors\Middlewares;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Http\Response\HttpMessage;
use SaQle\App;

class ApplyCorsHeadersMiddleware implements MiddlewareInterface {

     public function handle($request, $response = null): ?HttpMessage {

         $cors_headers = $request->attributes->get('cors_headers');
         $response->headers($cors_headers);

         return null;
     }
}