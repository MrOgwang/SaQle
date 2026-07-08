<?php
namespace SaQle\Core\Exceptions\Data;

use SaQle\Core\Exceptions\Abstracts\FrameworkException;
use SaQle\Core\FeedBack\FeedBack;
use Throwable;

/**
 * This exception is thrown when a data key is not found in the data or
 * request context object
 * */

class KeyNotFoundException extends FrameworkException {

     public function __construct(
         string $message = '', 
         array $context = [], 
         ?Throwable $prev = null
     ){
         parent::__construct(
             $message ?: $context['type']." key [".$context['key']."] does not exist!", 
             FeedBack::INTERNAL_SERVER_ERROR, 
             $context, 
             $prev
         );
     }
     
}
