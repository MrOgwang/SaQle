<?php

namespace SaQle\Console\Exceptions;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;

class MissingArgumentException extends AppException {

     public function __construct(string $name){
         $message = "The argument {$name} has not been provided!";
         parent::__construct($message, HttpStatus::NOT_ACCEPTABLE, [], null);
     }
}