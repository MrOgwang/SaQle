<?php
namespace SaQle\Orm\Database;

use SaQle\Core\Support\Db;
use SaQle\Orm\Database\Drivers\DbDriver;

final class DbProxy {
     public function __construct(
         protected Db $db
     ){}

     public function transaction(callable $callback) : mixed {
         return $this->db->run_transaction($callback);
     }

     public function driver() : DbDriver {
         return $this->db->resolve_driver();
     }
}
