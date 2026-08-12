<?php

namespace SaQle\Core\Modules;

use Closure;
use SaQle\Admin\Platform;

final class PlatformModuleBuilder {

     public function __construct(
         private Module $module
     ){}

     public function navigation(callable $callback) : static {

         Platform::navigation($callback);

         return $this;

     }

     public function resources(callable $callback) : static {

         Platform::resources($callback);

         return $this;

     }
     
}