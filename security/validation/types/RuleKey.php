<?php

namespace SaQle\Security\Validation\Types;

class RuleKey {
     public function __construct(
         public string $base,
         public bool $is_wildcards
     ) {}
}
