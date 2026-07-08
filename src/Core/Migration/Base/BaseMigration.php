<?php
namespace SaQle\Core\Migration\Base;

use SaQle\Core\Migration\Interfaces\IMigration;

abstract class BaseMigration implements IMigration{
     abstract public function get_migration_name() : string;
     abstract public function get_migration_timestamp() : int;
     abstract public function snapshots() : array;
     abstract public function down() : array;
     abstract public function up() : array;
}
