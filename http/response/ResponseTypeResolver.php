<?php
namespace SaQle\Http\Response;

use SaQle\Http\Request\Request;

final class ResponseTypeResolver {

     //Ordered by framework preference when q-values tie.
     private const SUPPORTED = [
        'text/event-stream'     => ResponseType::SSE,
        'application/json'      => ResponseType::JSON,
        'text/html'             => ResponseType::HTML,
        'application/xhtml+xml' => ResponseType::HTML,
        'application/xml'       => ResponseType::XML,
        'text/xml'              => ResponseType::XML,
        'text/plain'            => ResponseType::TEXT,
     ];

     public function resolve(Request $request): ResponseType {

         $accept = $request->header('Accept');
         $accept = $accept ? strtolower(trim((string)$accept)) : '*/*';

         /**
             * Explicit fetch/api heuristics FIRST.
             *
             * Browsers often send:
             * Accept: *\/*
             *
             * for fetch/ajax requests, which should not suddenly
             * become HTML
         */
         if($this->expects_json($request)){
             return ResponseType::JSON;
         }

         //Proper Accept negotiation.
         $accepted = $this->parse_accept_header($accept);

         foreach($accepted as $mime => $q){

             //exact match
             if(isset(self::SUPPORTED[$mime])){
                 return self::SUPPORTED[$mime];
             }

             //subtype wildcard: application/*
             if(str_contains($mime, '/*')){
                 [$type] = explode('/', $mime, 2);

                 foreach(self::SUPPORTED as $supported => $response_type){
                     if(str_starts_with($supported, $type.'/')) {
                         return $response_type;
                     }
                 }
             }

             //global wildcard
             if($mime === '*/*'){
                 if($this->expects_json($request)){
                     return ResponseType::JSON;
                 }

                 return ResponseType::HTML;
             }
         }

         //Final fallback heuristics.
         if($this->expects_json($request)){
             return ResponseType::JSON;
         }

         return ResponseType::HTML;
     }

     /**
         * Parse Accept header into ordered mime => q array.
         *
         * Example:
         *
         * Accept:
         * text/html,
         * application/xhtml+xml,
         * application/xml;q=0.9,
         * *\/*;q=0.8
         *
         * becomes:
         *
         * [
         *   'text/html' => 1.0,
         *   'application/xhtml+xml' => 1.0,
         *   'application/xml' => 0.9,
         *   '*\/*' => 0.8
         * ]
     */
     private function parse_accept_header(string $header): array {
         $results = [];

         foreach(explode(',', $header) as $part){
             $part = trim($part);
             if($part === ''){
                 continue;
             }

             $segments = array_map('trim', explode(';', $part));
             $mime = strtolower(array_shift($segments));
             $q = 1.0;

             foreach($segments as $segment){
                 if(str_starts_with($segment, 'q=')){
                     $q = (float)substr($segment, 2);
                     break;
                 }
             }

             $results[$mime] = $q;
         }

         //Sort descending by q-value.
         arsort($results, SORT_NUMERIC);

         return $results;
     }

     //detect modern fetch/ajax/api expectations
     private function expects_json(Request $request): bool {
         $fetch_mode = strtolower((string)$request->header('Sec-Fetch-Mode'));
         $fetch_dest = strtolower((string)$request->header('Sec-Fetch-Dest'));

         //Traditional ajax.
         if(strtolower((string)$request->header('X-Requested-With')) === 'xmlhttprequest'){
             return true;
         }

         //Fetch/XHR requests.
         if(in_array($fetch_mode, ['cors', 'same-origin'], true) && $fetch_dest !== 'document'){
             return true;
         }

         return false;
     }

     private function expects_html(): bool {
         $fetch_mode = strtolower((string)$request->header('Sec-Fetch-Mode'));
         $fetch_dest = strtolower((string)$request->header('Sec-Fetch-Dest'));

         if($fetch_mode === 'navigate' && $fetch_dest === 'document'){
             return true;
         }

         return false;
     }

     /*public function resolve(Request $request): ResponseType {

         if($request->accepts('text/event-stream')){
             return ResponseType::SSE;
         }

         if($request->accepts('application/json')){
            return ResponseType::JSON;
         }

         if($request->accepts('application/xml') || $request->accepts('text/xml')) {
             return ResponseType::XML;
         }

         if($request->accepts('text/plain')){
             return ResponseType::TEXT;
         }

         if($request->accepts('text/html')){
             return ResponseType::HTML;
         }

         $fetch_mode = $request->header('Sec-Fetch-Mode');
         $fetch_dest = $request->header('Sec-Fetch-Dest');

         if($fetch_mode === 'navigate' && $fetch_dest === 'document'){
             return ResponseType::HTML;
         }

         if(in_array($fetch_mode, ['cors', 'same-origin'], true) || $request->header('X-Requested-With') === 'XMLHttpRequest'){
             return ResponseType::JSON;
         }

         return ResponseType::HTML;
     }*/
}