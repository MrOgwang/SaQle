<?php

namespace SaQle\Console\Exceptions;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;

class MissingArgumentException extends FrameworkException {

     public function __construct(string $name){
         $message = "The argument {$name} has not been provided!";
         parent::__construct($message, FeedBack::NOT_ACCEPTABLE, [], null);
     }
}