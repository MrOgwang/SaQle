<?php

namespace SaQle\Core\Support;

use Attribute;

#[Attribute]
class HttpMethod {
    /** @param string[] $methods List of allowed HTTP verbs */
    public function __construct(public array $methods) {}
}