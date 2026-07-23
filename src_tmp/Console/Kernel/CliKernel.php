<?php

namespace SaQle\Console\Kernel;

use SaQle\App\Kernel;
use SaQle\Console\{
     CommandParser,
     CommandDispatcher
};

class CliKernel extends Kernel {

     public function process(mixed $options = null){

         //make the command context
         $parsed_command = CommandParser::parse($options);

         //dispatch command
         new CommandDispatcher($this->app()->commands)->dispatch($parsed_command);

     }
}
