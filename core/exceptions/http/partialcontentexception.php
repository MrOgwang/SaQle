<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Base\FatalException;
use SaQle\Core\FeedBack\FeedBack;

class PartialContentException extends FatalException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, FeedBack::PARTIAL_CONTENT, $data);
     }
}
