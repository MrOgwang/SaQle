<?php

namespace SaQle\Orm\Database;

use SaQle\Orm\Database\ColumnType;

class FieldDefinition {
     public string $name;
     public ColumnType $type;
     public ?int $length = null;
     public bool $primary = false;
     public bool $auto_increment = false;
     public bool $nullable = true;
     public mixed $default = null;
     public bool $auto_init_timestamp = false;
     public bool $auto_update_timestamp = false;
}
