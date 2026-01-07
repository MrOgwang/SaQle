<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

namespace SaQle\Auth\Guards\Attributes;

use Attribute;
use Exception;
use SaQle\Http\Response\HttpMessage;
use SaQle\Core\Exceptions\Http\UnauthorizedException;

#[Attribute(Attribute::TARGET_METHOD)]
class Allow {
     public function __construct(
         //the guards to evaluate
         protected array $guards, 

         //evaluation mode
         protected string $mode = 'all',

         //error message
         protected string $message = 'Unauthorized!'
     ){}

     public function enforce(){

         if(!$this->guards)
             return true;

         $results = [];

         foreach ($this->guards as $guard){
             $results[] = (bool)Guard::check($guard);
         }

         $passed = match($this->mode) {
             'all' => !in_array(false, $results, true),
             'any' => in_array(true, $results, true),
         };

         if(!$passed){
             throw new UnauthorizedException(message: $this->message);
         }
         
         return true;
     }
}

