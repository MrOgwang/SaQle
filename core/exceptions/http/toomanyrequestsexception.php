<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Base\HttpException;
use SaQle\Core\FeedBack\FeedBack;

class TooManyRequestsException extends HttpException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, FeedBack::TOO_MANY_REQUESTS, $data);
     }
}
