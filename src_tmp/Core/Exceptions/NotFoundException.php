<?php
namespace SaQle\Core\Exceptions;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;
use Throwable;

/**
 * Purpose: 
 * - User is not logged in or authentication failed
 * 
 * When it happens:
 * 1. Missing route
 * 2. Database record not found
 * 3. File not found
 * 
 * Typical response
 * - Redirect to 404
 * - Return 404 for Json APis
 * 
 * */

class NotFoundException extends AppException {
     public function __construct(
     	 string $message = '', 
     	 array $context = [], 
     	 ?Throwable $prev = null
     ){
     	 parent::__construct($message, HttpStatus::NOT_FOUND, $context, $prev);
     }
}
