<?php

namespace SaQle\Console\Exceptions;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

class UnknownCommandException extends AppException {

     public function __construct(string $command){
         $message = "The command {$command} is invalid!";
         parent::__construct($message, HttpStatus::NOT_ACCEPTABLE, [], null);
     }
}