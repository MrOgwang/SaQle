<?php
namespace SaQle\Http\Request;

use Throwable;
use SaQle\Middleware\MiddlewareGroup;
use SaQle\Http\Response\ResponseResolver;
use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Http\Response\{
     Response, 
     Message
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
         return (new ResponseResolver())->resolve($request, $result);
     }

     private function handle_exception(Throwable $e, Request $request) : void {

         $http_message = $e instanceof FrameworkException ? 
         $e->get_http_message() : 
         new Message(Message::INTERNAL_SERVER_ERROR, $e->getTrace(), $e->getMessage());

         /** 
          * Log the exception to file
          * 
          * Only when error logging is set to true in error config or
          * logging was explicitly requested.
          * 
          * NOTE: In the future one flag must be able to override the other
          * */
         if($http_message->should_log() || config('error.should_log')){
             log_to_file($e);
         }

         /**
          * Flash to session:
          * 
          * Only when flash was explicitly requested or 
          * request is unsafe: (PUT, POST, PATCH, DELETE)
          * */
         if($http_message->should_flash() || $request->is_unsafe()){
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
             $http_message->with_reload();
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

         //mark request for redirect
         $response = $this->resolve_response($request, $http_message);
         $response->send();

         $this->app()->set_stage(AppStage::TERMINATED);
     }

     private function execute_controller($request) : Message {

         /**
          * If this is a post, put, patch or delete request,
          * no aggregation should happen.
          * 
          * Execute the target component/controller and return an http_message
          * at once
          * */
         if($request->is_unsafe()){
             return ActionExecutor::execute($request);
         }

         /**
          * For get requests, do aggregate
          * only if required
          * */
         $aggregates = $request->route->layout ?? [];
         $components = [];
         foreach($aggregates as $name){
             $components[] = ComponentRegistry::get_definition($name);
         } 

         $components[] = $request->route->compiled_target;
         $aggregate_context = [];

         foreach($components as $comp){
             $http_message = ActionExecutor::execute($request, $comp->controller, $comp->method);
             $aggregate_context = array_merge($aggregate_context, $http_message->data);
         }

         return ok($aggregate_context);
     }

     public function handle(Request $request){
         try{

             //step 1: run request middleware
             $this->app()->set_stage(AppStage::REQUEST_BOOTSTRAP);
             $http_message = $this->bootstrap_request($request);
             /**
              * If request middleware returns an http_message,
              * short circuit the request flow immediatly
              * */
             if($http_message){
                 $this->short_circuit_response($request, $http_message);
                 return;
             }

             //step 2: execute the controller action
             $this->app()->set_stage(AppStage::REQUEST_RESOLUTION);
             $http_message = $this->execute_controller($request);
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
