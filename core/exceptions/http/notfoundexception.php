<?php
namespace SaQle\Core\Exceptions\Http;

use SaQle\Core\Exceptions\Base\FatalException;
use SaQle\Core\FeedBack\FeedBack;

class NotFoundException extends FatalException {
     public function __construct(string $message = '', array $data = []){
     	 parent::__construct($message, FeedBack::NOT_FOUND, $data);
     }
}
