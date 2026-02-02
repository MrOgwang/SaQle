<?php

namespace SaQle\Orm\Entities\Field\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ShouldValidate {
    public function __construct(
        public string $rule //the validation rule to apply
    ) {}
}
