<?php
namespace SaQle\Core\FeedBack;

class FeedBack {
	 const PROCESSING            = 102;
     const OK                    = 200;
     const CREATED               = 201;
     const NO_CONTENT            = 204;
     const PARTIAL_CONTENT       = 206;
     const MOVED_PERMANENTLY     = 301;
     const FOUND                 = 302;
     const BAD_REQUEST           = 400;
	 const UNAUTHORIZED          = 401;
     const PAYMENT_REQUIRED      = 402;
     const FORBIDDEN             = 403;
     const NOT_FOUND             = 404;
     const METHOD_NOT_ALLOWED    = 405;
     const NOT_ACCEPTABLE        = 406;
     const REQUEST_TIMEOUT       = 408;
     const CONFLICT              = 409;
     const TOO_MANY_REQUESTS     = 429;
     const INTERNAL_SERVER_ERROR = 500;
     const SERVICE_UNAVAILABLE   = 503;

     public protected(set) int $code {
     	 set(int $value){
     	 	 $this->code = $value;
     	 }

     	 get => $this->code;
     }

     public protected(set) mixed $data {
     	 set(mixed $value){
     	 	 $this->data = $value;
     	 }

     	 get => $this->data;
     }

     public protected(set) string $message {
     	 set(string $value){
     	 	 $this->message = $value;
     	 }

     	 get => $this->message;
     }

     public protected(set) string $action {
     	 set(string $value){
     	 	 $this->action = $value;
     	 }

     	 get => $this->action;
     }

	 public function __construct(){
	 	 $this->code    = FeedBack::OK;
	 	 $this->message = $this->get_message($this->code);
	 	 $this->data    = null;
	 	 $this->action  = '';
	 }

	 public function set(int $code, mixed $data = null, ?string $message = null, string $action = ''){
	 	 $this->code    = $code;
	 	 $this->message = $message ? $message : $this->get_message($this->code);
	 	 $this->data    = $data;
	 	 $this->action  = $action;
	 }

	 protected function get_message(int $code){
		 $status_code = [
		 	102 => 'Processing',
	        200 => 'Success',
	        201 => 'Created successfully',
	        204 => 'No content',
	        206 => 'Partial content',
	        301 => 'Moved permanently',
	        302 => 'Found',
	        400 => 'Bad Request',
	        401 => 'Unauthorized',
	        402 => 'Payment required',
	        403 => 'Forbidden',
	        404 => 'Not Found',
	        405 => 'Method Not Allowed',
	        406 => 'Not Acceptable – You requested a format that isn’t json',
	        408 => 'Request timeout',
	        409 => 'Conflict',
	        429 => 'Too Many Requests – You’re requesting too many kittens! Slow down!',
	        500 => 'Internal Server Error – We had a problem with our server. Try again later.',
	        503 => 'Service Unavailable – We’re temporarily offline for maintenance. Please try again later.'
	     ];
	     return $status_code[$code];
	}
}
