<?php

namespace SaQle\Http\Kernel;

use Throwable;
use SaQle\App\Kernel;
use SaQle\Http\Response\{
     Response,
     ResponseResolver
};
use SaQle\Http\Message\Message;
use SaQle\Http\Request\Execution\ActionExecutor;
use SaQle\Http\Request\Request;
use SaQle\Auth\Context\ActorContext;
use SaQle\Middleware\HttpMiddlewarePipeline;
use SaQle\Core\Support\{
     AppStage,
     Session
};

class HttpKernel extends Kernel {

     private function bootstrap_request(Request $request) : ? Message {
         date_default_timezone_set(config('app.timezone'));
         return HttpMiddlewarePipeline::run('before', $request, null);
     }

     private function bootstrap_response(Request $request, Response $response) : ?Message {
         return HttpMiddlewarePipeline::run('after', $request, $response);
     }

     private function response(Request $request, Message $result, bool $first_pass = false) : Response {

         $response = (new ResponseResolver())->resolve($request, $result);

         if($first_pass){
             $this->app()->set_stage(AppStage::RESPONSE_RESOLUTION);
         }

         //run response middleware
         $result = $this->bootstrap_response($request, $response);
         if($first_pass){
             $this->app()->set_stage(AppStage::RESPONSE_BOOTSTRAP);
         }
 
         if(!$result->is_success()){
             $response = $this->response($request, $result);
         } 

         return $response;
     }

     public function process(mixed $options = null){

         try{

             //create the request
             $request = RequestFactory::make();

             //start session
             Session::start($request);

             $this->app()->set_stage(AppStage::REQUEST_START);

             //run middleware pipeline
             $result = $this->bootstrap_request($request);
             $this->app()->set_stage(AppStage::REQUEST_BOOTSTRAP);

             if($result->is_success()){

                 //set the actor
                 ActorContext::set_user($request->user);

                 //execute controller
                 $result = ActionExecutor::execute($request);

                 $this->app()->set_stage(AppStage::REQUEST_RESOLUTION);
             }

             $response = $this->response($request, $result, true);
            
         }catch(Throwable $e){

             $result = ExceptionHandler::handle($request, $e);

             $response = $this->response($request, $result);

         }finally{

             //close session  
             Session::close($request);

             //send the response
             $response->send(); 
             $this->app()->set_stage(AppStage::RESPONSE_SEND);

             $this->app()->set_stage(AppStage::TERMINATED);
         }  
     }
}
