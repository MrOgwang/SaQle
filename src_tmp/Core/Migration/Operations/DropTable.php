<?php

namespace SaQle\Core\Migration\Operations;

use SaQle\Core\Migration\Interfaces\MigrationOperation;
use SaQle\Console\Cli;

class DropTable implements MigrationOperation {

     public function __construct(private Cli $cli){

     }

     public function execute(array $operation, mixed $dbdriver, mixed $snapshot) : mixed {

         $table_name = $operation['params']['name'];

         $this->cli->info("Attempting to drop table: {$table_name}!\n");

         $tbldropped = $dbdriver->drop_table($table_name);

         if(!$tbldropped){

             $this->cli->error("Table {$table_name} deletion failed!\n");

             return false;
         }

         $this->cli->success("Table {$table_name} deleted!\n");

         return true;
     }
    
}