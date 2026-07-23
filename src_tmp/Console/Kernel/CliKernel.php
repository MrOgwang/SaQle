<?php

namespace SaQle\Console\Kernel;

use SaQle\App\Kernel;
use SaQle\Console\{
     CommandParser,
     CommandDispatcher
};
use SaQle\Auth\Context\ActorContext;
use SaQle\Auth\Models\CliUser;

class CliKernel extends Kernel {

     public function process(mixed $options = null){

         ActorContext::set_user(new CliUser());

         //make the command context
         $parsed_command = CommandParser::parse($options);

         //dispatch command
         new CommandDispatcher($this->app()->commands)->dispatch($parsed_command);

     }
}
