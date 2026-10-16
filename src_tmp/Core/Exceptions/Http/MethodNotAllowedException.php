<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

class MethodNotAllowedException extends AppException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, HttpStatus::METHOD_NOT_ALLOWED, $data);
     }
}
