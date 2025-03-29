<?php

use SaQle\Core\Services\Container\Container;
use SaQle\Log\FileLogger;
use SaQle\Http\Response\{HttpMessage, StatusCode};
use SaQle\Http\Response\Types\RedirectResponse;
use SaQle\FeedBack\ExceptionFeedBack;

if(!function_exists('resolve')){
     function resolve(string $abstract, array $parameters = []){
         return Container::init()->resolve($abstract, $parameters);
     }
}

if(!function_exists('file_logger')){
     function file_logger(){
        return resolve(FileLogger::class);
     }
}

if(!function_exists('ok_message')){
     function ok_message(mixed $data = null, string $message = ''){
         return new HttpMessage(code: StatusCode::OK, response: $data, message: $message);
     }
}

if(!function_exists('payment_required_message')){
     function payment_required_message(mixed $data = null, string $message = ''){
         return new HttpMessage(code: StatusCode::OK, response: $data, message: $message);
     }
}

if(!function_exists('redirect')){
     function redirect(?string $url = null, int $status = StatusCode::FOUND->value, mixed $data = null, ?string $message = null){
         return new RedirectResponse(url: $url, status: $status)->send();
     }
}

if(!function_exists('from_feedback')){
     function from_feedback(array $keys){
         $feedback = ExceptionFeedBack::init();
         return $feedback::extract($keys);
     }
}

if(!function_exists('import_routes')){
     function import_routes(string $app, string $type = 'web'){
         $path = DOCUMENT_ROOT.'/apps/'.$app.'/routes/'.$type.'.php';
         if(file_exists($path)){
             return require $path;
         }

         return [];
     }
}

?>