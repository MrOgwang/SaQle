<?php
namespace SaQle\Core\Exceptions;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;
use Throwable;

/**
 * Purpose: 
 * - The request conflicts with current system state
 * 
 * Examples
 * 1. API rate limits
 * 2. OTP requested too many times
 * 3. Brute force login protection
 * 
 * Typical response
 * - Reload page with message
 * - Return 429 status for APIs
 * 
 * */

class RateLimitException extends AppException {
     public function __construct(
     	 string $message = '', 
     	 array $context = [], 
     	 ?Throwable $prev = null
     ){
         parent::__construct($message, HttpStatus::TOO_MANY_REQUESTS, $context, $prev);
     }
}
