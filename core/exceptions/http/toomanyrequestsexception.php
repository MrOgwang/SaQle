<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\RateLimitException;

class TooManyRequestsException extends RateLimitException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, $data);
     }
}
