<?php
namespace SaQle\Core\Support;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Listens {
     public function __construct(
         public string|array $events
     ) {}
}
