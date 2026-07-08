<?php
namespace SaQle\Auth\Exceptions;

use SaQle\Core\Exceptions\AuthenticationException as BaseAuthenticationException;

class AuthenticationException extends BaseAuthenticationException {
     public function __construct(string $message = '',  array $context = []){
         parent::__construct($message, $context);
     }
}
