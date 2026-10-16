<?php
namespace SaQle\Core\Exceptions;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;
use Throwable;

/**
 * Purpose: 
 * - User is loggedin but not allowed to perform an action
 * 
 * When it happens:
 * 1. Insufficient role and/or permissions
 * 
 * Typical response
 * - Redirect to 403 page
 * - Return 403 for Json APis
 * 
 * */

class AuthorizationException extends AppException {
     public function __construct(
         string $message = '', 
         array $context = [], 
         ?Throwable $prev = null
     ){
         parent::__construct($message, HttpStatus::UNAUTHORIZED, $context, $prev);
     }
}
