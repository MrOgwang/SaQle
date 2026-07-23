<?php

namespace SaQle\App;

use Closure;

final class ConsoleAppBuilder extends AppBuilder {

     public function commands(Closure $callback): self {

         $builder = new CommandsBuilder();

         $callback($builder);

         $this->setup->commands = $builder->get();

         return $this;
     }

     public function middleware(Closure $callback) : self {

         $builder = new MiddlewareBuilder();

         $callback($builder);

         $this->setup->console_middleware = $builder;

         return $this;
     }
}