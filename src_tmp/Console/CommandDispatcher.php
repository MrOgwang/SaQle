<?php

namespace SaQle\Console;

use SaQle\Console\Signature\SignatureBinder;
use SaQle\Console\Exceptions\UnknownCommandException;

class CommandDispatcher {
     public function __construct(
         protected CommandRegistry $registry
     ){}

     public function dispatch(ParsedCommand $parsed) : int {

         $definition = $this->registry->find($parsed->command);

         if(!$definition){
             throw new UnknownCommandException($parsed->command);
         }

         $command = resolve($definition->class);

         $signature = $command->signature();

         $context = new SignatureBinder()->bind($parsed, $signature);

         return $command->handle($context);

     }

}