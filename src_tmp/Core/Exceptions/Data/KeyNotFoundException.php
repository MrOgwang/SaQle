<?php
namespace SaQle\Core\Exceptions\Data;

use SaQle\Core\Exceptions\Abstracts\AppException;
use SaQle\Http\HttpStatus;
use Throwable;

/**
 * This exception is thrown when a data key is not found in the data or
 * request context object
 * */

class KeyNotFoundException extends AppException {

     public function __construct(
         string $message = '', 
         array $context = [], 
         ?Throwable $prev = null
     ){
         parent::__construct(
             $message ?: $context['type']." key [".$context['key']."] does not exist!", 
             HttpStatus::INTERNAL_SERVER_ERROR, 
             $context, 
             $prev
         );
     }
     
}
