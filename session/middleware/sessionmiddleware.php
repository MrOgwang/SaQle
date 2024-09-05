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
           ini_set('session.gc_maxlifetime', 3600 * 24 * 3); //keep session data for three days.
           ini_set('session.cookie_lifetime', 3600 * 24 * 3); //keep the session cookie for three days
           ini_set('session.gc_probability', 1); //run garbage collection more frequently
           ini_set('session.gc_divisor', 100);
           if(session_status() == PHP_SESSION_NONE){
                session_start();
           }

           /**
           * Set error reporting
           */
           ini_set('display_errors', DISPLAY_ERRORS);
           ini_set('display_startup_errors', DISPLAY_STARTUP_ERRORS);
     	 parent::handle($request);
      }
}
?>