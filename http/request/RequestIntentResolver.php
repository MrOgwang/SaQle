<?php
namespace SaQle\Http\Request;

use RuntimeException;
use SaQle\Middleware\MiddlewareRequestInterface;

final class RequestIntentResolver{
     public function resolve(MiddlewareRequestInterface $request): RequestIntent {
         //1. SSE: transport-level exclusivity
         if($request->accepts('text/event-stream')){
             return RequestIntent::SSE;
         }

         //2. URL prefix intent (explicit contracts)
         if($request->path_starts_with(config('app.sse_url_prefixes'))){
             return RequestIntent::SSE;
         }

         if($request->path_starts_with(config('app.api_url_prefixes'))) {
             return RequestIntent::API;
         }

         //3. Modern browser fetch semantics
         $fetch_mode = $request->header('Sec-Fetch-Mode');
         $fetch_dest = $request->header('Sec-Fetch-Dest');

         //Browser navigation
         if($fetch_mode === 'navigate' && $fetch_dest === 'document'){
             return RequestIntent::WEB;
         }

         //Browser JS (fetch / XHR)
         if(in_array($fetch_mode, ['cors', 'same-origin'], true)){
             return RequestIntent::AJAX;
         }

         //4. Legacy AJAX detection
         if($request->header('X-Requested-With') === 'XMLHttpRequest'){
             return RequestIntent::AJAX;
         }

         //5. Content negotiation
         if($request->accepts('application/json')){
             return RequestIntent::API;
         }

         if($request->accepts('text/html')) {
             return RequestIntent::WEB;
         }

         // 6. Final safe default
         return RequestIntent::WEB;
     }
}
