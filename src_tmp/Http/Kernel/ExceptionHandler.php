<?php

namespace SaQle\Http\Kernel;

use Throwable;
use SaQle\Http\Message\{
	 Message,
	 RedirectMessage
};
use SaQle\Http\Request\Request;
use SaQle\Http\HttpStatus;
use SaQle\Core\Exceptions\Abstracts\AppException;

final class ExceptionHandler { 

	 private static function build_http_message(Request $request, Throwable $e){

         $code    = HttpStatus::INTERNAL_SERVER_ERROR;
         $message = "Unexpected error occured";
         $context = [];

         if($e instanceof AppException){   
             $code    = $e->getCode();
             $message = $e->getSafeMessage();
             $context = $e->getContext();
         }

	 	 if($request->is_unsafe() && $request->expects_html()){
	 	 	 return new RedirectMessage($request->uri())
             ->notify('error', $message)
             ->data($context, true);
	 	 }

         return Message::make($code, $message, $context);
     }

     private static function apply_error_attributes(Request $request, Message $msg){
         
         $request->attributes->set('error.code', $msg->get_code());
         $request->attributes->set('error.message', $msg->get_message());
         $request->attributes->set('error.context', $msg->get_data());

     }

	 private static function log_exception(Throwable $e){
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

	 public static function handle(Request $request, Throwable $e) : Message {

	 	 self::log_exception($e);

	 	 $message = self::build_http_message($request, $e);

	 	 self::apply_error_attributes($request, $message);

	 	 return $message; 
	 }
}