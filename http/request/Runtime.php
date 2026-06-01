<?php
namespace SaQle\Http\Request;

use Throwable;
use SaQle\Middleware\MiddlewareGroup;
use SaQle\Http\Response\ResponseResolver;
use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Http\Response\{
     Response, 
     Message,
     RedirectMessage,
     FileMessage,
     ResponseType
};
use SaQle\Core\Support\{
     AppContext,
     AppStage
};
use SaQle\Http\Request\Execution\ActionExecutor;
use SaQle\Core\Registries\ComponentRegistry;

class Runtime {

     private function app() {
         return AppContext::get();
     }

     private function bootstrap_request(Request $request) : ?Message {
         date_default_timezone_set(config('app.timezone'));
         return (new MiddlewareGroup())->handle_incoming($request, null);
     }

     private function bootstrap_response(Request $request, Response $response) : ?Message {
         return (new MiddlewareGroup())->handle_outgoing($request, $response);
     }

     private function resolve_response(Request $request, Message $result) : Response {

         /**
          * Flash results to session:
          * 
          * Don't flash for requests that expect a json response
          * or non redirect results
          * */
         if($request->expects_html() && $result instanceof RedirectMessage && $result->should_flash()){

         }

         return (new ResponseResolver())->resolve($request, $result);
     }

     private function handle_exception(Throwable $e, Request $request) : void {

         $http_message = $e instanceof FrameworkException ? 
         $e->get_http_message() : 
         new Message(Message::INTERNAL_SERVER_ERROR, $e->getTrace(), $e->getMessage());

         $request->attributes->set('error.code', $http_message->code);
         $request->attributes->set('error.message', $http_message->message);
         $request->attributes->set('error.context', $http_message->data);

         /** 
          * Log the exception to file
          * 
          * Only when error logging is set to true in error config or
          * logging was explicitly requested.
          * 
          * NOTE: In the future one flag must be able to override the other
          * */
         if(config('error.should_log')){
             log_to_file($e);
         }

         /**
          * Flash to session:
          * 
          * Only when flash was explicitly requested or 
          * request is unsafe: (PUT, POST, PATCH, DELETE)
          * */
         if($request->is_unsafe()){
             $request->session->set('flash', (object)[
                 'message' => $http_message->message,
                 'context' => $http_message->data,
                 'code'    => $http_message->code,
                 'type'    => 'error'
             ], true);
         }
         

         /**
          * For unsafe requests: (PUT, POST, PATCH, DELETE), 
          * reload same page after flashing
          * */
         if($request->is_unsafe()){
             
         } 

         /*$stage = $this->app()->get_stage();

         if($stage === AppStage::REQUEST_BOOTSTRAP){
             log_to_file('Inside middleware!');
         }

         if($stage === AppStage::REQUEST_RESOLUTION){
             // Controller / route failure
         }

         if($stage === AppStage::RESPONSE_BOOTSTRAP){
             // Response transformation failure
         }*/

         $response = $this->resolve_response($request, $http_message);

         $response->send();
     }

     private function short_circuit_response($request, $http_message){

         $request->attributes->set('error.code', $http_message->code);
         $request->attributes->set('error.message', $http_message->message);
         $request->attributes->set('error.context', $http_message->data);

         $response = $this->resolve_response($request, $http_message);
         $response->send();
     }

     public function handle(Request $request){
         try{

             //step 1: run request middleware
             $this->app()->set_stage(AppStage::REQUEST_BOOTSTRAP);
             $http_message = $this->bootstrap_request($request);
             if($http_message){
                 $this->short_circuit_response($request, $http_message);
                 return;
             } 

             //step 2: execute the controller action
             $this->app()->set_stage(AppStage::REQUEST_RESOLUTION);
             $http_message = ActionExecutor::execute($request);
             $response = $this->resolve_response($request, $http_message);

             //step 3: run response middleware
             $this->app()->set_stage(AppStage::RESPONSE_BOOTSTRAP);
             $http_message = $this->bootstrap_response($request, $response);

             /**
              * If response middleware returns an http_message, 
              * override the response from controller execution.
              * 
              * Note: I am feeling uneasy about allowing developers
              * to override responses from controllers in response middleware,
              * as this turns middleware into some kind of controllers as well,
              * so this might be disallowed in future once the benefits are 
              * properly weighed!
              * */
             if($http_message){
                 $this->short_circuit_response($request, $http_message);
                 return;
             }

             //step 4: send the response from normal flow
             $response->send(); 

             $this->app()->set_stage(AppStage::TERMINATED);

         }catch(Throwable $e){
             $this->handle_exception($e, $request);
         }
     }
}
