<?php
namespace SaQle\Session\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;

class SessionMiddleware extends IMiddleware{
      public function handle(MiddlewareRequestInterface &$request){
           /**
           * Set the session handler and start session.
           */
     	 $handler_class = SESSION_HANDLER;
           if($handler_class){
                session_set_save_handler(new $handler_class(), true);
           }
           ini_set('session.cookie_domain', SESSION_DOMAIN);
           session_start();

           /**
           * Set error reporting
           */
           ini_set('display_errors', DISPLAY_ERRORS);
           ini_set('display_startup_errors', DISPLAY_STARTUP_ERRORS);
     	 parent::handle($request);
      }
}
?>