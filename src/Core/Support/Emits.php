<?php
namespace SaQle\Core\Support;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Emits {
     public function __construct(
         public array $before = [],
         public array $after = []
     ) {}
}
