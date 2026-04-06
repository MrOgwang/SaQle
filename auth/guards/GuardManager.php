<?php

namespace SaQle\Auth\Guards;

use Closure;

final class GuardManager {
     public function add(string $name, Closure $rule, ?Closure $onfail = null): void {
         Guard::add($name, $rule, $onfail);
     }
}
