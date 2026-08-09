<?php

namespace SaQle\Core\Modules;

use Closure;
use SaQle\Admin\Admin;

final class AdminModuleBuilder {

     public function __construct(
         private Module $module
     ){}

     public function navigation(callable $callback) : static {

         Admin::navigation($callback);

         return $this;

     }

     public function resources(callable $callback) : static {

         Admin::resources($callback);

         return $this;

     }
     
}