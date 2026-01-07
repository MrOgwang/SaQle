<?php
namespace SaQle\Core\Events\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class Emits {
     public function __construct(
         public array $before = [],
         public array $after = []
     ) {}
}
