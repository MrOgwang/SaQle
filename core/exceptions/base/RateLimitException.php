<?php
namespace SaQle\Core\Exceptions\Base;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;
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

class RateLimitException extends FrameworkException {
     public function __construct(
     	 string $message = '', 
     	 array $context = [], 
     	 ?Throwable $prev = null
     ){
         parent::__construct($message, FeedBack::TOO_MANY_REQUESTS, $context, $prev);
     }
}
