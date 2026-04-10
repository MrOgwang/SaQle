<?php
namespace SaQle\Http\Response;

use SaQle\Http\Request\{
     Request, 
     RequestScope
};

final class ResponseTypeResolver {
     public function resolve(Request $request): ResponseType {

         if($this->looks_like_redirect($request)){
             return ResponseType::REDIRECT;
         }

         if($this->looks_like_file_request($request)){
             return ResponseType::FILE;
         }

         if($request->route->restype){
             return $request->route->restype;
         }

         if($request->accepts('text/event-stream')){
             return ResponseType::SSE;
         }

         if($request->accepts('application/json')){
            return ResponseType::JSON;
         }

         if($request->accepts('text/html')){
             return ResponseType::HTML;
         }

         if($request->accepts('application/xml') || $request->accepts('text/xml')) {
             return ResponseType::XML;
         }

         if($request->accepts('text/plain')){
             return ResponseType::TEXT;
         }

         //defaults

         $fetch_mode = $request->header('Sec-Fetch-Mode');
         $fetch_dest = $request->header('Sec-Fetch-Dest');

         if(($fetch_mode === 'navigate' && $fetch_dest === 'document') || $request->is_web_request()){
             return ResponseType::HTML;
         }

         if(in_array($fetch_mode, ['cors', 'same-origin'], true) || $request->header('X-Requested-With') === 'XMLHttpRequest' || $request->is_api_request()){
             return ResponseType::JSON;
         }

         return ResponseType::HTML;
     }

     private function looks_like_redirect(Request $request) : bool {
         return false;
     }

     private function looks_like_file_request(Request $request) : bool {
         $target_component = $request->route->compiled_target->name;
         
         if(in_array($target_component, ['protectedfile', 'staticfile'])){
             return true;
         }

         return false;
     }
}