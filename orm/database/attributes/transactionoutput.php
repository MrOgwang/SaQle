<?php
declare(strict_types = 0);

namespace SaQle\Orm\Database\Attributes;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
class TransactionOutput {
    public function __construct(public string $name) {}
}

?>