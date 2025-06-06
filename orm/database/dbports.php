<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database;

enum DbPorts : int {
    case MYSQL = 3306;
    case PGSQL = 5432;
}
