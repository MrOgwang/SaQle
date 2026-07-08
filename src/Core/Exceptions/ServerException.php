<?php
namespace SaQle\Core\Exceptions;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;
use Throwable;

/**
 * Purpose: 
 * - Something unexpected broke internally
 * 
 * Examples
 * 1. Third party API failed
 * 2. Database inconsistency
 * 3. Unexpected logic state
 * 
 * Typical response
 * - Redirect to 500 error page
 * - Log stack trace
 * - Return 500 status for APIs
 * 
 * */

class ServerException extends FrameworkException {
     public function __construct(
     	 string $message = '', 
     	 array $context = [], 
     	 ?Throwable $prev = null
     ){
         parent::__construct($message, FeedBack::INTERNAL_SERVER_ERROR, $context, $prev);
     }
}
