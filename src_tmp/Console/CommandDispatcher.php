<?php

namespace SaQle\Console;

use SaQle\Console\Signature\SignatureBinder;
use SaQle\Console\Exceptions\UnknownCommandException;
use SaQle\Middleware\ConsoleMiddlewarePipeline;

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

         ConsoleMiddlewarePipeline::run('before', $context, $definition->middleware);

         return $command->handle($context);

     }

}