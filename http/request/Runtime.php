<?php
namespace SaQle\Http\Request;

use Throwable;
use SaQle\Middleware\Factory\MiddlewareGroup;
use SaQle\Log\FileLogger;
use SaQle\Http\Request\Execution\ActionExecutor;
use SaQle\Http\Response\ResponseResolver;
use SaQle\Core\Exceptions\ExceptionMapper;

class Runtime {
     private function bootstrap_request(Request $request) : Request {
         date_default_timezone_set(config('app.timezone'));
         return (new MiddlewareGroup())->handle($request);
     }

     private function execute_action(Request $request) {
         $compiled_target = $request->route->compiled_target;
         return (new ActionExecutor())->execute($request, $compiled_target[1], $compiled_target[2]);
     }

     private function resolve_response(Request $request, $result) {
         return (new ResponseResolver())->resolve($request, $result);
     }

     private function handle_exception(Throwable $e, Request $request): void {
         $logger = new FileLogger( path_join([config('base_path'), 'logs', 'errors.txt']) );
         $timestamp = time();
         $time = date("g:i A", $timestamp);
         $logger->log_to_file($time." -- ".$e."\n\n");

         $result = (new ExceptionMapper())->map($e, $request);
         $response = $this->resolve_response($request, $result);
         $response->send();
     }

     public function handle(Request $request){
         try{
             $request  = $this->bootstrap_request($request);
             $result   = $this->execute_action($request);
             $response = $this->resolve_response($request, $result);
             $response->send();
         }catch(Throwable $e){
            $this->handle_exception($e, $request);
         }
     }
}
