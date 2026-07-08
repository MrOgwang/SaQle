<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The cors middleware checks that the request obeys cors configurations
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Http\Cors\Middlewares;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Http\Response\Message;
use SaQle\App;

class CorsMiddleware implements MiddlewareInterface {

     public function handle($request, $response = null) : ?Message {
         $app         = app();
         $origins     = $app->cors->get_origins();
         $headers     = $app->cors->get_headers();
         $methods     = $app->cors->get_methods();
         $credentials = $app->cors->allows_credentials();

         $origin = $request->header('Origin');

         if(!$origin){
             return null;
         }

         //Determine allowed origin
         $allowed_origin = null;

         if(in_array('*', $origins)){
             $allowed_origin = $credentials ? $origin : '*';
         }elseif(in_array($origin, $origins)){
             $allowed_origin = $origin;
         }
       
         if(!$allowed_origin){
             return null;
         }

         $cors_headers = [
             'Access-Control-Allow-Origin'  => $allowed_origin,
             'Access-Control-Allow-Methods' => implode(', ', $methods),
             'Vary'                         => 'Origin',
         ];

         $req_headers = $request->header('Access-Control-Request-Headers');

         if(in_array('*', $headers) && $req_headers){
             $cors_headers['Access-Control-Allow-Headers'] = $req_headers;
         }else{
             $cors_headers['Access-Control-Allow-Headers'] = implode(', ', $headers);
         }

         if($credentials){
             $cors_headers['Access-Control-Allow-Credentials'] = 'true';
         }

         $cors_headers['Access-Control-Max-Age'] = '86400';

         $request->attributes->set('cors_headers', $cors_headers);

         if($request->method() === 'OPTIONS'){
             return no_content();
         }

         //Headers will be attached to response later (response middleware phase)
         
         return null;
     }
}
