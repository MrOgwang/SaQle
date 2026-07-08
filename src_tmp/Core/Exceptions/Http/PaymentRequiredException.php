<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;

class PaymentRequiredException extends FrameworkException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, FeedBack::PAYMENT_REQUIRED, $data);
     }
}
