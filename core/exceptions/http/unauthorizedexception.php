<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Base\FeedbackException;
use SaQle\Core\FeedBack\FeedBack;

class UnauthorizedException extends FeedbackException {
     public function __construct(string $message, array $data = [], string $redirect = ''){
     	 parent::__construct($message, FeedBack::UNAUTHORIZED, $data, $redirect);
     }
}
?>