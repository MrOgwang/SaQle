<?php
declare(strict_types = 1);
namespace SaQle\Dao\DbContext;
enum DbTypes : string {
    case MYSQL = "mysql";
    case PGSQL = "pgsql";
}
?>