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
 * The session middleware 
 * 1. starts the session for web requests
 * 2. restores the session user to request object if the user is in session
 * 3. some web requests are started from the app where the user had loggedin,
 *    to maintain that session these requests come with http_authorization header set as those in api requests.
 *    In such cases authenticate the request here and restore session.
 * 
 * a web app depending on which route was matched
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Session\Middleware;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Auth\Observers\SigninObserver;
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Log\FileLogger;
use SaQle\Core\FeedBack\ExceptionFeedBack;

class SessionMiddleware extends IMiddleware{
      public function handle(MiddlewareRequestInterface &$request){
           //Set the session handler and start session.
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

           //Set error reporting
           ini_set('display_errors', DISPLAY_ERRORS);
           ini_set('display_startup_errors', DISPLAY_STARTUP_ERRORS);

           $logger = new FileLogger(DOCUMENT_ROOT."/logs/log.txt", "w+");
           $logger->log_to_file(json_encode($_SERVER));

           if(!$request->user && isset($_SERVER['HTTP_REQUIRES_AUTH'])){
                $request->enforce_permissions = true;
                $auth_backend_class = AUTH_BACKEND_CLASS;
                $service = new $auth_backend_class('jwt');
                new SigninObserver($service);
                $fb = $service->authenticate();
                if($fb->code === FeedBack::OK && $fb->data){
                     $request->context->set('user', $fb->data['user'], true);
                }
           }

           //create a feedback exception object from previous request
           $fbex = $request->context->get('FeedbackException', null);
           if($fbex){
                $efb = ExceptionFeedBack::init();
                $efb->set($fbex->code, $fbex->data, $fbex->message);
                $request->context->remove('FeedbackException');
           }
           
     	 parent::handle($request);
     }
}
?>