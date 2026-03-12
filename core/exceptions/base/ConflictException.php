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
 * 1. Duplicate email
 * 2. User name already taken
 * 3. Resource already exists
 * 4. Booking already taken
 * 
 * Typical response
 * - Reload page with message
 * - Return conflict status for APIs
 * 
 * */

class ConflictException extends FrameworkException {
     public function __construct(
     	 string $message = '', 
     	 array $context = [], 
     	 ?Throwable $prev = null
     ){
         parent::__construct($message, FeedBack::CONFLICT, $data, $prev);
     }
}
