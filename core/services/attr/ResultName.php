<?php
namespace SaQle\Core\Services\Attr;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ResultName {
    public function __construct(public string $name) {}
}
