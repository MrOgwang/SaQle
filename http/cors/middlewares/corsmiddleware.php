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

           $origin = $request->header('Origin') ?? '*';

           //Determine allowed origin
           $allowed_origin = null;

           if(in_array('*', $origins)){
                $allowed_origin = '*';
           }elseif(in_array($origin, $origins)){
                $allowed_origin = $origin;
           }
           
           if(!$allowed_origin){
                return forbidden(message: 'CORS not allowed');
           }

           //Build headers array
           $cors_headers = [];

           if($allowed_origin){
                $cors_headers['Access-Control-Allow-Origin'] = $allowed_origin;
           }

           $cors_headers['Access-Control-Allow-Methods'] = implode(', ', $methods);

           if(in_array('*', $headers)){
                $cors_headers['Access-Control-Allow-Headers'] = '*';
           }else{
                $cors_headers['Access-Control-Allow-Headers'] = implode(', ', $headers);
           }

           if($credentials){
                $cors_headers['Access-Control-Allow-Credentials'] = 'true';
           }

           //short circuit for preflight
           if($request->method() === 'OPTIONS'){
                return no_content();
           }

           //For normal requests → attach headers later (response middleware phase)
           $request->attributes->set('cors_headers', $cors_headers);

           return null;
      }
}
