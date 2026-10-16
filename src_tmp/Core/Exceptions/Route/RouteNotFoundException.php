<?php
namespace SaQle\Core\Exceptions\Route;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

/**
 * This exception is thrown when a route matching
 * the incoming request is not found
 * */
class RouteNotFoundException extends AppException {
     public function __construct(string $message = '', array $data = []){
         parent::__construct($message, HttpStatus::NOT_FOUND, $data);
     }
}
