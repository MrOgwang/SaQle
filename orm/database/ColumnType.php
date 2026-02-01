<?php
declare(strict_types=1);

namespace SaQle\Orm\Database;

enum ColumnType: string {

     //textual types
     case CHAR = 'char'; // fixed-length string
     case TEXT = 'text'; // other text

     //integer types
     case INTEGER = 'integer';
     
     //float types
     case FLOAT = 'float';
     case DECIMAL = 'decimal';
     case DOUBLE = 'double';

     //boolean
     case BOOLEAN = 'boolean';

     //date and time
     case DATE = 'date';
     case TIME = 'time';
     case DATETIME = 'datetime';

     //identifiers
     case UUID = 'uuid';

     //json
     case JSON = 'json';
}
