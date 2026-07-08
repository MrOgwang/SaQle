<?php

namespace SaQle\Orm\Entities\Field\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FieldDefinition {
    public function __construct(
        private ?string $key = null // optional override for array key
    ) {}

    public function set_key(string $key){
        $this->key = $key;
    }

    public function get_key(){
         return $this->key;
    }
}
