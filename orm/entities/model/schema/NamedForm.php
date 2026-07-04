<?php

namespace SaQle\Orm\Entities\Model\Schema;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class NamedForm{
     public function __construct(
         public readonly ?string $name = null
     ) {}
}
