<?php
namespace SaQle\Auth\Exceptions;

use SaQle\Core\Exceptions\AuthorizationException as BaseAuthorizationException;

class AuthorizationException extends BaseAuthorizationException {
     public function __construct(string $message = '',  array $context = []){
         parent::__construct($message, $context);
     }
}
