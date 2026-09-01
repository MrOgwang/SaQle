<?php

namespace SaQle\Core\Support;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class SchemaIndex {
     public function __construct(
         public int $index
     ) {}
}
