<?php

namespace SaQle\Auth\Guards;

use Closure;

class AuthorizationResult {
     public function __construct(
         public bool $passed,
         public ?string $failed_guard = null,
         public ?Closure $on_fail = null,
         public array $failed_guards = [] // useful for "any"
     ){}
}