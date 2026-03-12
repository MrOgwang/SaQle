<?php
namespace SaQle\Http\Request;

use Throwable;
use SaQle\Middleware\Factory\MiddlewareGroup;
use SaQle\Http\Response\ResponseResolver;
use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Http\Response\{HttpResponse, HttpMessage};

class Runtime {

     private function bootstrap_request(Request $request) : Request {
         date_default_timezone_set(config('app.timezone'));
         return (new MiddlewareGroup())->handle($request);
     }

     private function resolve_response(Request $request, ?HttpMessage $result = null) : HttpResponse {
         return (new ResponseResolver())->resolve($request, $result);
     }

     private function handle_exception(Throwable $e, Request $request) : void {
         log_to_file($e);

         if($e instanceof FrameworkException){
             $log_exception = $e->get_log();
             if($log_exception){
                 log_to_file($e);
             }

             $flash_exception = $e->get_flash();
             if($flash_exception){
                 $request->session->set('flash', (object)[
                     'message' => $e->getMessage()
                 ], true);
             }

             $http_message = new HttpMessage($e->getCode(), $e->get_context(), $e->getMessage());

             $http_message->redirect($e->get_redirect());
         }else{
             $http_message = new HttpMessage(HttpMessage::INTERNAL_SERVER_ERROR, $e->getTrace(), $e->getMessage());

             $http_message->redirect("/error/500");
         }

         $response = $this->resolve_response($request, $http_message);

         $response->send();
     }

     public function handle(Request $request){
         try{

             $request  = $this->bootstrap_request($request);
             $response = $this->resolve_response($request);
             $response->send();

         }catch(Throwable $e){
             $this->handle_exception($e, $request);
         }
     }
}
