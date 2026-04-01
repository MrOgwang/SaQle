<?php
namespace SaQle\Core\Exceptions;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;
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

class AuthorizationException extends FrameworkException {
     public function __construct(
         string $message = '', 
         array $context = [], 
         ?Throwable $prev = null
     ){
         parent::__construct($message, FeedBack::FORBIDDEN, $context, $prev);
     }
}
