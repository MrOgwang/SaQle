<?php

namespace SaQle\Core\Migration\Operations;

use SaQle\Core\Migration\Interfaces\MigrationOperation;
use SaQle\Console\Cli;

class UpdateUnique implements MigrationOperation {

     public function __construct(private Cli $cli){

     }

     public function execute(array $operation, mixed $dbdriver, mixed $snapshot) : mixed {

         $table_name = $operation['params']['name'];

         $this->cli->info("Attempting to update unique constraints on table: {$table_name}!\n");

         $dbdriver->add_unique_constraints($table_name, $operation['unique'], $operation['prev_unique']);

         return true;
     }
    
}