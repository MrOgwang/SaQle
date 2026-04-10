<?php
namespace SaQle\Http\Request;

use Throwable;
use SaQle\Middleware\MiddlewareGroup;
use SaQle\Http\Response\ResponseResolver;
use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Http\Response\{Response, HttpMessage};

class Runtime {

     private function bootstrap_request(Request $request) : Request {
         date_default_timezone_set(config('app.timezone'));
         return (new MiddlewareGroup())->handle_incoming($request, null);
     }

     private function bootstrap_response(Request $request, Response $response) : Response {
         return (new MiddlewareGroup())->handle_outgoing($request, $response);
     }

     private function resolve_response(Request $request, ?HttpMessage $result = null) : Response {
         return (new ResponseResolver())->resolve($request, $result);
     }

     private function handle_exception(Throwable $e, Request $request) : void {

         log_to_file($e);

         if($e instanceof FrameworkException){

             $http_message = $e->get_http_message();

             $flash_response = $http_message->get_flash();

             if($flash_response){

                 $request->session->set('flash', (object)[
                     'message' => $http_message->message,
                     'context' => $http_message->data,
                     'code'    => $http_message->code,
                     'type'    => 'error'
                 ], true);

             }

         }else{
             $http_message = new HttpMessage(HttpMessage::INTERNAL_SERVER_ERROR, $e->getTrace(), $e->getMessage());

             $http_message->redirect(route('app.error', ['code' => $http_message->code]));
         }

         $response = $this->resolve_response($request, $http_message);

         $response->send();
     }

     public function handle(Request $request){
         try{
            
             $request  = $this->bootstrap_request($request);
             $response = $this->resolve_response($request);
             $response = $this->bootstrap_response($request, $response);

             $response->send();

         }catch(Throwable $e){
             $this->handle_exception($e, $request);
         }
     }
}
