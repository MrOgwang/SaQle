<?php
namespace SaQle\Core\Exceptions\Data;

use SaQle\Core\Exceptions\Base\FatalException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when a data key is not found in the data or
 * request context object
 * */

class KeyNotFoundException extends FatalException {

     public function __construct(string $key, array $context = []){
         parent::__construct(
             message   : "Data item key [{$key}] does not exist!",
             code      : FeedBack::BAD_REQUEST,
             context   : $context
         );
     }
}
