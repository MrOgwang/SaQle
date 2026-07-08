<?php
namespace SaQle\Core\Exceptions\Route;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when a route matching
 * the incoming request is not found
 * */
class RouteNotFoundException extends FrameworkException {
     public function __construct(string $message = '', array $data = []){
         parent::__construct($message, FeedBack::NOT_FOUND, $data);
     }
}
