<?php

namespace SaQle\Http\Kernel;

use Throwable;
use SaQle\App\Kernel;
use SaQle\Http\Response\ResponseResolver;
use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Http\Response\{
     Response, 
     Message,
     RedirectMessage
};
use SaQle\Core\Support\{ 
     AppContext,
     AppStage
};
use SaQle\Http\Request\Execution\ActionExecutor;
use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Core\Exceptions\ValidationException;
use SaQle\Http\Request\{
     Request,
     ErrorContext
};
use SaQle\Auth\Context\ActorContext;
use SaQle\Middleware\HttpMiddlewarePipeline;

class HttpKernel extends Kernel {

     private function bootstrap_request(Request $request) : ? Message {
         date_default_timezone_set(config('app.timezone'));
         return HttpMiddlewarePipeline::run('before', $request, null);
     }

     private function bootstrap_response(Request $request, Response $response) : ?Message {
         return HttpMiddlewarePipeline::run('after', $request, $response);
     }

     private function resolve_response(Request $request, Message $result) : Response {
         return (new ResponseResolver())->resolve($request, $result);
     }

     private function log_exception(Throwable $e){
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
     }

     private function build_error_context(Throwable $e, Request $request): ErrorContext {
         $ctx = new ErrorContext();

         $ctx->should_redirect = $request->is_unsafe() && $request->expects_html();

         if($e instanceof ValidationException){
             $ctx->should_flash_input = true;
             $ctx->should_flash_errors = true;
             $ctx->input_payload = $e->input();
             $ctx->errors_payload = $e->errors();
             return $ctx;
         }

         $ctx->should_flash_input = $ctx->should_redirect;
         $ctx->should_flash_errors = $ctx->should_redirect;
         $ctx->errors_payload = $e->getMessage();
         $ctx->input_payload = null;

         return $ctx;
     }

     private function build_http_message(Throwable $e, ErrorContext $ctx, Request $request) {
         if($ctx->should_redirect){
             return new RedirectMessage($request->uri());
         }

         if($e instanceof FrameworkException){
             return $e->get_http_message();
         }

         return new Message(Message::INTERNAL_SERVER_ERROR, $e->getTrace(), $e->getMessage());
     }

     private function apply_flash(Request $request, Message $msg, ?ErrorContext $ctx = null) : void { 

         $should_flash_input = $ctx && $ctx->should_flash_input ? true : $msg->should_flash();
         $should_flash_errors = $ctx && $ctx->should_flash_errors ? true : $msg->should_flash();
         $errors_payload = $ctx ? $ctx->errors_payload : $msg->message;
         $input_payload = $ctx ? $ctx->input_payload : $msg->data;

         $flash = [];
         if($should_flash_input){
             Session::flash('__old', $input_payload);
         }

         if($should_flash_errors){
             Session::flash('__errors', $errors_payload);
         }
     }

     private function apply_request_attributes(Request $request, Message $msg){
         if($request->is_safe()){
             $request->attributes->set('error.code', $msg->code);
             $request->attributes->set('error.message', $msg->message);
             $request->attributes->set('error.context', $msg->data);
         }
     }

     private function handle_exception(Throwable $e, Request $request): void {
         
         $this->log_exception($e);

         $context = $this->build_error_context($e, $request);

         $message = $this->build_http_message($e, $context, $request);

         $this->apply_flash($request, $message, $context);

         $this->apply_request_attributes($request, $message);

         $response = $this->resolve_response($request, $message);

         $response->send();
     }

     private function short_circuit_response($request, $http_message){

         $this->apply_request_attributes($request, $http_message);

         $response = $this->resolve_response($request, $http_message);
         $response->send();
     }

     public function process(mixed $options = null){

         $this->app()->set_stage(AppStage::REQUEST_BOOTSTRAP);

         //create the request
         $request = RequestFactory::make();

         if(str_starts_with($request->uri(), '/saqle/')){
             ActorContext::to_platform();
         }

         try{

             //start session
             Session::start($request);

             //run middleware pipeline
             $req_bootstrap_msg = $this->bootstrap_request($request);
             if($req_bootstrap_msg){
                 $this->apply_flash($request, $req_bootstrap_msg);
                 $this->short_circuit_response($request, $req_bootstrap_msg);
                 return;
             }

             if($request->user){
                 ActorContext::set_user($request->user);
             }

             //step 2: execute the controller action
             $this->app()->set_stage(AppStage::REQUEST_RESOLUTION);
             $act_exec_msg = ActionExecutor::execute($request);
             $response = $this->resolve_response($request, $act_exec_msg);

             //step 3: run response middleware
             $this->app()->set_stage(AppStage::RESPONSE_BOOTSTRAP);
             $res_bootstrap_msg = $this->bootstrap_response($request, $response);

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
             if($res_bootstrap_msg){
                 $this->apply_flash($request, $res_bootstrap_msg);
                 $this->short_circuit_response($request, $res_bootstrap_msg);
                 return;
             }

             //step 4: send the response from normal flow
             $this->apply_flash($request, $act_exec_msg);
             $response->send(); 

             $this->app()->set_stage(AppStage::TERMINATED);

             //close session
             Session::close($request);

         }catch(Throwable $e){
             $this->handle_exception($e, $request);
         }
     }
}
