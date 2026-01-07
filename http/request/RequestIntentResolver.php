<?php
namespace SaQle\Http\Request;

use RuntimeException;
use SaQle\Middleware\MiddlewareRequestInterface;

final class RequestIntentResolver{
     public function resolve(MiddlewareRequestInterface $request): RequestIntent {
         //1. SSE: transport-level exclusivity
         if($request->accepts('text/event-stream')){
             if(!$request->route->supports('sse')){
                 throw new RuntimeException('Route does not support SSE');
             }
             return RequestIntent::SSE;
         }

         //2. URL prefix intent (explicit contracts)
         if($request->path_starts_with(SSE_URL_PREFIXES)){
             if ($request->route->supports('sse')) {
                 return RequestIntent::SSE;
             }
         }

         if($request->path_starts_with(API_URL_PREFIXES)) {
             if($request->route->supports('json')) {
                 return RequestIntent::API;
             }
         }

         //3. Modern browser fetch semantics
         $fetch_mode = $request->header('Sec-Fetch-Mode');
         $fetch_dest = $request->header('Sec-Fetch-Dest');

         //Browser navigation
         if($fetch_mode === 'navigate' && $fetch_dest === 'document'){
             if($request->route->supports('html')){
                 return RequestIntent::WEB;
             }
         }

         //Browser JS (fetch / XHR)
         if(in_array($fetch_mode, ['cors', 'same-origin'], true)){
             if($request->route->supports('json')){
                 return RequestIntent::AJAX;
             }
         }

         //4. Legacy AJAX detection
         if($request->header('X-Requested-With') === 'XMLHttpRequest'){
             if($request->route->supports('json')){
                 return RequestIntent::AJAX;
             }
         }

         //5. Content negotiation
         if($request->accepts('application/json') && $request->route->supports('json')){
             return RequestIntent::API;
         }

         if($request->accepts('text/html') && $request->route->supports('html')) {
             return RequestIntent::WEB;
         }

         //6. Safe defaults (route-guided)
         if($request->route->supports('html')){
             return RequestIntent::WEB;
         }

         if($request->route->supports('json')){
             return RequestIntent::API;
         }

         throw new RuntimeException('406 Not Acceptable');
     }
}
