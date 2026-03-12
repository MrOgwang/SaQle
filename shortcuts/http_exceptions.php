<?php

use SaQle\Core\Exceptions\Http\{
     BadRequestException, 
     UnauthorizedException, 
     PaymentRequiredException, 
     ForbiddenException, 
     MethodNotAllowedException, 
     NotAcceptableException, 
     RequestTimeoutException, 
     TooManyRequestsException, 
     InternalServerErrorException, 
     ServiceUnavailableException
 };

use SaQle\Core\Exceptions\Base\{NotFoundException, ConflictException};

/**
 * The following are shortcuts to http exceptions
 * */
function create_exception(string $eclass, string $message = '', mixed $data = null){
     return new $eclass($message, is_null($data) ? [] : (!is_array($data) ? [$data] : $data));
}

if(!function_exists('payment_required_exception')){
     function payment_required_exception(string $message = '', mixed $data = null){
         return create_exception(PaymentRequiredException::class, $message, $data);
     }
}

if(!function_exists('bad_request_exception')){
     function bad_request_exception(string $message = '', mixed $data = null){
         return create_exception(BadRequestException::class, $message, $data);
     }
}

if(!function_exists('unauthorized_exception')){
     function unauthorized_exception(string $message = '', mixed $data = null){
         return create_exception(UnauthorizedException::class, $message, $data);
     }
}

if(!function_exists('forbidden_exception')){
     function forbidden_exception(string $message = '', mixed $data = null){
         return create_exception(ForbiddenException::class, $message, $data);
     }
}

if(!function_exists('not_found_exception')){
     function not_found_exception(string $message = '', mixed $data = null){
         return create_exception(NotFoundException::class, $message, $data);
     }
}

if(!function_exists('method_not_allowed_exception')){
     function method_not_allowed_exception(string $message = '', mixed $data = null){
         return create_exception(MethodNotAllowedException::class, $message, $data);
     }
}

if(!function_exists('not_acceptable_exception')){
     function not_acceptable_exception(string $message = '', mixed $data = null){
        return create_exception(NotAcceptableException::class, $message, $data);
     }
}

if(!function_exists('request_timeout_exception')){
     function request_timeout_exception(string $message = '', mixed $data = null){
         return create_exception(RequestTimeoutException::class, $message, $data);
     }
}

if(!function_exists('conflict_exception')){
     function conflict_exception(string $message = '', mixed $data = null){
         return create_exception(ConflictException::class, $message, $data);
     }
}

if(!function_exists('too_many_requests_exception')){
     function too_many_requests_exception(string $message = '', mixed $data = null){
         return create_exception(TooManyRequestsException::class, $message, $data);
     }
}

if(!function_exists('internal_server_error_exception')){
     function internal_server_error_exception(string $message = '', mixed $data = null){
         return create_exception(InternalServerErrorException::class, $message, $data);
     }
}

if(!function_exists('service_unavailable_exception')){
     function service_unavailable_exception(string $message = '', mixed $data = null){
         return create_exception(ServiceUnavailableException::class, $message, $data);
     }
}
