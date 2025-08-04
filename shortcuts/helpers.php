<?php

use SaQle\Core\Services\Container\Container;
use SaQle\Log\FileLogger;
use SaQle\Http\Response\HttpMessage;
use SaQle\Http\Response\Types\RedirectResponse;
use SaQle\FeedBack\ExceptionFeedBack;
use SaQle\Core\Exceptions\Http\{ProcessingException, OkException, CreatedException, NoContentException, BadRequestException, PartialContentException, MovedPermanentlyException, FoundException, UnauthorizedException, PaymentRequiredException, ForbiddenException, NotFoundException, MethodNotAllowedException, NotAcceptableException, RequestTimeoutException, ConflictException, TooManyRequestsException, InternalServerErrorException, ServiceUnavailableException};
use SaQle\Core\FeedBack\FeedBack;
use SaQle\Http\Request\Request;

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

/**
 * The following are shortcuts to http message responses/feedback
 * */
function create_feedback(int $code, mixed $data = null, string $message = '', string $action = ''){
     $fb = new FeedBack();
     $fb->set($code, $data, $message, $action);
     return $fb;
}

if(!function_exists('ok')){
     function ok(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::OK, $data, $message, $action);
     }
}

if(!function_exists('processing')){
     function processing(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::PROCESSING, $data, $message, $action);
     }
}

if(!function_exists('payment_required')){
     function payment_required(mixed $data = null, string $message = '', string $action = ''){
         return create_feedback(FeedBack::PAYMENT_REQUIRED, $data, $message, $action);
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

/**
 * The following are shortcuts to http exceptions
 * */
function create_exception(string $eclass, string $message = '', string $redirect = '', mixed $data = null){
     return new $eclass($message, is_null($data) ? [] : (!is_array($data) ? [$data] : $data), $redirect);
}

if(!function_exists('ok_exception')){
     function ok_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(OkException::class, $message, $redirect, $data);
     }
}

if(!function_exists('processing_exception')){
     function processing_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(ProcessingException::class, $message, $redirect, $data);
     }
}

if(!function_exists('payment_required_exception')){
     function payment_required_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(PaymentRequiredException::class, $message, $redirect, $data);
     }
}

if(!function_exists('created_exception')){
     function created_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(CreatedException::class, $message, $redirect, $data);
     }
}

if(!function_exists('no_content_exception')){
     function no_content_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(NoContentException::class, $message, $redirect, $data);
     }
}

if(!function_exists('partial_content_exception')){
     function partial_content_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(PartialContentException::class, $message, $redirect, $data);
     }
}

if(!function_exists('moved_permanently_exception')){
     function moved_permanently_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(MovedPermanentlyException::class, $message, $redirect, $data);
     }
}

if(!function_exists('found_exception')){
     function found_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(FoundException::class, $message, $redirect, $data);
     }
}

if(!function_exists('bad_request_exception')){
     function bad_request_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(BadRequestException::class, $message, $redirect, $data);
     }
}

if(!function_exists('unauthorized_exception')){
     function unauthorized_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(UnauthorizedException::class, $message, $redirect, $data);
     }
}

if(!function_exists('forbidden_exception')){
     function forbidden_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(ForbiddenException::class, $message, $redirect, $data);
     }
}

if(!function_exists('not_found_exception')){
     function not_found_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(NotFoundException::class, $message, $redirect, $data);
     }
}

if(!function_exists('method_not_allowed_exception')){
     function method_not_allowed_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(MethodNotAllowedException::class, $message, $redirect, $data);
     }
}

if(!function_exists('not_acceptable_exception')){
     function not_acceptable_exception(string $message = '', string $redirect = '', mixed $data = null){
        throw create_exception(NotAcceptableException::class, $message, $redirect, $data);
     }
}

if(!function_exists('request_timeout_exception')){
     function request_timeout_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(RequestTimeoutException::class, $message, $redirect, $data);
     }
}

if(!function_exists('conflict_exception')){
     function conflict_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(ConflictException::class, $message, $redirect, $data);
     }
}

if(!function_exists('too_many_requests_exception')){
     function too_many_requests_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(TooManyRequestsException::class, $message, $redirect, $data);
     }
}

if(!function_exists('internal_server_error_exception')){
     function internal_server_error_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(InternalServerErrorException::class, $message, $redirect, $data);
     }
}

if(!function_exists('service_unavailable_exception')){
     function service_unavailable_exception(string $message = '', string $redirect = '', mixed $data = null){
         throw create_exception(ServiceUnavailableException::class, $message, $redirect, $data);
     }
}



if(!function_exists('redirect')){
     function redirect(?string $url = null, int $status = HttpMessage::FOUND, mixed $data = null, ?string $message = null){
         return new RedirectResponse(url: $url, status: $status)->send();
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

if(!function_exists('to_context')){
     function to_context(string $key, Closure $data_source, bool $session = false){
         $data = $data_source();
         $request = Request::init();
         $request->context->set($key, $data, $session);
         return $data;
     }
}

if(!function_exists('from_context')){
     function from_context(string $key, ?Closure $data_source = null, bool $latest = false){
         $request = Request::init();
         if(!$request->context->exists($key) || $latest)
             return $data_source ? $data_source() : null;

         return $request->context->get_or_fail($key);
     }
}

