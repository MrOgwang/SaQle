<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Base\HttpException;
use SaQle\Core\FeedBack\FeedBack;

class ServiceUnavailableException extends HttpException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, FeedBack::SERVICE_UNAVAILABLE, $data);
     }
}
