<?php

use SaQle\Core\Exceptions\Http\{
     BadRequestException, 
     PaymentRequiredException, 
     ForbiddenException, 
     MethodNotAllowedException, 
     NotAcceptableException, 
     RequestTimeoutException, 
     TooManyRequestsException, 
     InternalServerErrorException, 
     ServiceUnavailableException
};

use SaQle\Core\Exceptions\Data\{
     KeyNotFoundException
};

use SaQle\Core\Exceptions\Database\{
     DatabaseNotFoundException,
     TableNotFoundException
};

use SaQle\Auth\Exceptions\{
     AuthorizationException,
     AuthenticationException
};

use SaQle\Core\Exceptions\{
     ValidationException,
     NotFoundException, 
     ConflictException,
     RateLimitException,
     ServerException
};

function create_exception(string $eclass, string $message = '', mixed $data = null){
     return new $eclass($message, is_null($data) ? [] : (!is_array($data) ? [$data] : $data));
}

if(!function_exists('database_not_found_exception')){
     function database_not_found_exception(string $message = '', mixed $data = null){
         return create_exception(DatabaseNotFoundException::class, $message, $data);
     }
}

if(!function_exists('table_not_found_exception')){
     function table_not_found_exception(string $message = '', mixed $data = null){
         return create_exception(TableNotFoundException::class, $message, $data);
     }
}

/*if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}

if(!function_exists('')){
     function (string $message = '', mixed $data = null){
         return create_exception(::class, $message, $data);
     }
}*/











if(!function_exists('key_not_found_exception')){
     function key_not_found_exception(string $message = '', mixed $data = null){
         return create_exception(KeyNotFoundException::class, $message, $data);
     }
}

if(!function_exists('bad_request_exception')){
     function bad_request_exception(string $message = '', mixed $data = null){
         return create_exception(BadRequestException::class, $message, $data);
     }
}

if(!function_exists('authorization_exception')){
     function authorization_exception(string $message = '', mixed $data = null){
         return create_exception(AuthorizationException::class, $message, $data);
     }
}

if(!function_exists('authentication_exception')){
     function authentication_exception(string $message = '', mixed $data = null){
         return create_exception(AuthenticationException::class, $message, $data);
     }
}

if(!function_exists('payment_required_exception')){
     function payment_required_exception(string $message = '', mixed $data = null){
         return create_exception(PaymentRequiredException::class, $message, $data);
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

if(!function_exists('validation_exception')){
     function validation_exception(string $message = '', mixed $data = null){
         return create_exception(ValidationException::class, $message, $data);
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

if(!function_exists('rate_limit_exception')){
     function rate_limit_exception(string $message = '', mixed $data = null){
         return create_exception(RateLimitException::class, $message, $data);
     }
}

if(!function_exists('server_exception')){
     function server_exception(string $message = '', mixed $data = null){
         return create_exception(ServerException::class, $message, $data);
     }
}
