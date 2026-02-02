<?php

namespace SaQle\Orm\Entities\Field\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FieldDefinition {
    public function __construct(
        public ?string $key = null // optional override for array key
    ) {}
}
