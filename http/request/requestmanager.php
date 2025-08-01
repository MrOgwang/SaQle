<?php
namespace SaQle\Http\Request;

use SaQle\Http\Request\Processors\{ApiRequestProcessor, SseRequestProcessor, WebRequestProcessor};
use SaQle\Middleware\Factory\MiddlewareGroup;
use SaQle\Log\FileLogger;

class RequestManager{
	 public function __construct(private Request $request){}

     public function process(){
         try{
             date_default_timezone_set(DEFAULT_TIMEZONE);
             (new MiddlewareGroup())->handle($this->request);

             if($this->request->is_api_request()){
                 $processor = new ApiRequestProcessor();
                 $processor->process();
             }elseif($this->request->is_sse_request()){
                 
             }else{
                 $processor = new WebRequestProcessor();
                 $processor->process();
             }
         }catch(\Exception $e){
             $logger = new FileLogger(DOCUMENT_ROOT."/logs/errors.txt");
             $timestamp = time();
             $time = date("g:i A", $timestamp);
             $logger->log_to_file($time." -- ".$e."\n\n");
         }
     }
}
