<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database;

enum DbTypes : string {
    case MYSQL = "mysql";
    case PGSQL = "pgsql";
}
