<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

class ServiceUnavailableException extends AppException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, HttpStatus::SERVICE_UNAVAILABLE, $data);
     }
}
