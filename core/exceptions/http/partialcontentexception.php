<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Base\FeedbackException;
use SaQle\Core\FeedBack\FeedBack;

class PartialContentException extends FeedbackException {
     public function __construct(string $message, array $data = [], string $redirect = ''){
     	 parent::__construct($message, FeedBack::PARTIAL_CONTENT, $data, $redirect);
     }
}
?>