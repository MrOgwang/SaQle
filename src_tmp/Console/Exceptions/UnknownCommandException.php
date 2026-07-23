<?php

namespace SaQle\Console\Exceptions;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;

class UnknownCommandException extends FrameworkException {

     public function __construct(string $command){
         $message = "The command {$command} is invalid!";
         parent::__construct($message, FeedBack::NOT_ACCEPTABLE, [], null);
     }
}