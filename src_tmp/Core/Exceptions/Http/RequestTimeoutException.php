<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

class RequestTimeoutException extends AppException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, HttpStatus::REQUEST_TIMEOUT, $data);
     }
}
