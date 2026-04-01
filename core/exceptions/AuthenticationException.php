<?php
namespace SaQle\Core\Exceptions;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;
use Throwable;

/**
 * Purpose: 
 * - User is not logged in or authentication failed
 * 
 * When it happens:
 * 1. Login failure
 * 2. Missing session
 * 3. Invalid token
 * 4. Expired session
 * 
 * Typical response
 * - Redirect to login
 * - Return 401 for Json APis
 * 
 * */

class AuthenticationException extends FrameworkException {

     public function __construct(
         string $message = '', 
         array $context = [], 
         ?Throwable $prev = null
     ){
         parent::__construct($message, FeedBack::UNAUTHORIZED, $context, $prev);

         $this->redirect_url = config('auth.route', null);
     }
}
