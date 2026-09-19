<?php

namespace SaQle\Orm\Entities\Model\Schema;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class NamedPresenter{
     public function __construct(
         public readonly ?string $name = null
     ) {}
}
