<?php

namespace SaQle\Console\Signature;

use SaQle\Console\{
     ParsedCommand,
     CommandContext
};
use SaQle\Console\Exceptions\MissingArgumentException;

class SignatureBinder {
     public function bind(ParsedCommand $parsed, Signature $signature) : CommandContext {
         $arguments = [];

         foreach($signature->arguments() as $index => $argument){
             if(array_key_exists($index, $parsed->arguments)){

                 $arguments[$argument->name] = $parsed->arguments[$index];

                 continue;
             }

             if($argument->required){
                 throw new MissingArgumentException($argument->name);
             }

             $arguments[$argument->name] = $argument->default;
         }

         $options = [];

         foreach ($signature->options() as $name => $option){

             if(array_key_exists($name, $parsed->options)){

                 $options[$name] = $parsed->options[$name];

                 continue;
             }

             $options[$name] = $option->default;
         }

         return new CommandContext(
             command: $parsed->command,
             arguments: $arguments,
             options: $options,
             raw: $parsed->raw
         );
     }
}