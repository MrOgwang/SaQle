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

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\App;

class CorsMiddleware extends IMiddleware{
      public function handle(MiddlewareRequestInterface &$request){
           $app         = App::init();
           $origins     = $app::cors()::getorigins();
           $headers     = $app::cors()::getheaders();
           $methods     = $app::cors()::getmethods();
           $credentials = $app::cors()::getcredentials();

           $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

           if(in_array('*', $origins)){
                header("Access-Control-Allow-Origin: *");
           }elseif(in_array($origin, $origins)) {
                header("Access-Control-Allow-Origin: $origin");
           }

           header("Access-Control-Allow-Methods: ".implode(', ', $methods));

           if(in_array('*', $headers)){
               header("Access-Control-Allow-Headers: *");
           }else{
               header("Access-Control-Allow-Headers: ".implode(', ', $headers));
           }

           if($credentials){
                header("Access-Control-Allow-Credentials: true");
           }

           //Handle OPTIONS request (Preflight)
           if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
                http_response_code(204);
                exit;
           }

     	 parent::handle($request);
      }
}
