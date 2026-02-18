<?php

use SaQle\Core\Exceptions\Http\{ProcessingException, OkException, CreatedException, NoContentException, BadRequestException, PartialContentException, MovedPermanentlyException, FoundException, UnauthorizedException, PaymentRequiredException, ForbiddenException, NotFoundException, MethodNotAllowedException, NotAcceptableException, RequestTimeoutException, ConflictException, TooManyRequestsException, InternalServerErrorException, ServiceUnavailableException};

/**
 * The following are shortcuts to http exceptions
 * */
function create_exception(string $eclass, string $message = '', mixed $data = null){
     return new $eclass($message, is_null($data) ? [] : (!is_array($data) ? [$data] : $data));
}

if(!function_exists('ok_exception')){
     function ok_exception(string $message = '', mixed $data = null){
         throw create_exception(OkException::class, $message, $data);
     }
}

if(!function_exists('processing_exception')){
     function processing_exception(string $message = '', mixed $data = null){
         throw create_exception(ProcessingException::class, $message, $data);
     }
}

if(!function_exists('payment_required_exception')){
     function payment_required_exception(string $message = '', mixed $data = null){
         throw create_exception(PaymentRequiredException::class, $message, $data);
     }
}

if(!function_exists('created_exception')){
     function created_exception(string $message = '', mixed $data = null){
         throw create_exception(CreatedException::class, $message, $data);
     }
}

if(!function_exists('no_content_exception')){
     function no_content_exception(string $message = '', mixed $data = null){
         throw create_exception(NoContentException::class, $message, $data);
     }
}

if(!function_exists('partial_content_exception')){
     function partial_content_exception(string $message = '', mixed $data = null){
         throw create_exception(PartialContentException::class, $message, $data);
     }
}

if(!function_exists('moved_permanently_exception')){
     function moved_permanently_exception(string $message = '', mixed $data = null){
         throw create_exception(MovedPermanentlyException::class, $message, $data);
     }
}

if(!function_exists('found_exception')){
     function found_exception(string $message = '', mixed $data = null){
         throw create_exception(FoundException::class, $message, $data);
     }
}

if(!function_exists('bad_request_exception')){
     function bad_request_exception(string $message = '', mixed $data = null){
         throw create_exception(BadRequestException::class, $message, $data);
     }
}

if(!function_exists('unauthorized_exception')){
     function unauthorized_exception(string $message = '', mixed $data = null){
         throw create_exception(UnauthorizedException::class, $message, $data);
     }
}

if(!function_exists('forbidden_exception')){
     function forbidden_exception(string $message = '', mixed $data = null){
         throw create_exception(ForbiddenException::class, $message, $data);
     }
}

if(!function_exists('not_found_exception')){
     function not_found_exception(string $message = '', mixed $data = null){
         throw create_exception(NotFoundException::class, $message, $data);
     }
}

if(!function_exists('method_not_allowed_exception')){
     function method_not_allowed_exception(string $message = '', mixed $data = null){
         throw create_exception(MethodNotAllowedException::class, $message, $data);
     }
}

if(!function_exists('not_acceptable_exception')){
     function not_acceptable_exception(string $message = '', mixed $data = null){
        throw create_exception(NotAcceptableException::class, $message, $data);
     }
}

if(!function_exists('request_timeout_exception')){
     function request_timeout_exception(string $message = '', mixed $data = null){
         throw create_exception(RequestTimeoutException::class, $message, $data);
     }
}

if(!function_exists('conflict_exception')){
     function conflict_exception(string $message = '', mixed $data = null){
         throw create_exception(ConflictException::class, $message, $data);
     }
}

if(!function_exists('too_many_requests_exception')){
     function too_many_requests_exception(string $message = '', mixed $data = null){
         throw create_exception(TooManyRequestsException::class, $message, $data);
     }
}

if(!function_exists('internal_server_error_exception')){
     function internal_server_error_exception(string $message = '', mixed $data = null){
         throw create_exception(InternalServerErrorException::class, $message, $data);
     }
}

if(!function_exists('service_unavailable_exception')){
     function service_unavailable_exception(string $message = '', mixed $data = null){
         throw create_exception(ServiceUnavailableException::class, $message, $data);
     }
}
