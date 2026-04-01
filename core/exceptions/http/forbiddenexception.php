<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;

class ForbiddenException extends FrameworkException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, FeedBack::FORBIDDEN, $data);
     }
}
