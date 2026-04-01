<?php

use SaQle\Core\FeedBack\FeedBack;
use SaQle\Http\Response\HttpMessage;

/**
 * The following are shortcuts to http message responses/feedback
 * */
function create_feedback(int $code, mixed $data = null, string $message = '', string $action = ''){
     $fb = new FeedBack();
     $fb->set($code, $data, $message, $action);
     return HttpMessage::from_feedback($fb);
}

if(!function_exists('processing')){
     function processing(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::PROCESSING, $data, $message, $action);
     }
}

if(!function_exists('ok')){
     function ok(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::OK, $data, $message, $action);
     }
}

if(!function_exists('created')){
     function created(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::CREATED, $data, $message, $action);
     }
}

if(!function_exists('no_content')){
     function no_content(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::NO_CONTENT, $data, $message, $action);
     }
}

if(!function_exists('partial_content')){
     function partial_content(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::PARTIAL_CONTENT, $data, $message, $action);
     }
}

if(!function_exists('moved_permanently')){
     function moved_permanently(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::MOVED_PERMANENTLY, $data, $message, $action);
     }
}

if(!function_exists('found')){
     function found(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::FOUND, $data, $message, $action);
     }
}

if(!function_exists('bad_request')){
     function bad_request(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::BAD_REQUEST, $data, $message, $action);
     }
}

if(!function_exists('unauthorized')){
     function unauthorized(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::UNAUTHORIZED, $data, $message, $action);
     }
}

if(!function_exists('payment_required')){
     function payment_required(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::PAYMENT_REQUIRED, $data, $message, $action);
     }
}

if(!function_exists('forbidden')){
     function forbidden(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::FORBIDDEN, $data, $message, $action);
     }
}

if(!function_exists('not_found')){
     function not_found(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::NOT_FOUND, $data, $message, $action);
     }
}

if(!function_exists('method_not_allowed')){
     function method_not_allowed(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::METHOD_NOT_ALLOWED, $data, $message, $action);
     }
}

if(!function_exists('not_acceptable')){
     function not_acceptable(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::NOT_ACCEPTABLE, $data, $message, $action);
     }
}

if(!function_exists('request_timeout')){
     function request_timeout(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::REQUEST_TIMEOUT, $data, $message, $action);
     }
}

if(!function_exists('conflict')){
     function conflict(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::CONFLICT, $data, $message, $action);
     }
}

if(!function_exists('too_many_requests')){
     function too_many_requests(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::TOO_MANY_REQUESTS, $data, $message, $action);
     }
}

if(!function_exists('internal_server_error')){
     function internal_server_error(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::INTERNAL_SERVER_ERROR, $data, $message, $action);
     }
}

if(!function_exists('service_unavailable')){
     function service_unavailable(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::SERVICE_UNAVAILABLE, $data, $message, $action);
     }
}