<?php

namespace SaQle\Core\Migration\Operations;

use SaQle\Core\Migration\Interfaces\MigrationOperation;
use SaQle\Console\Cli;

class DropColumns implements MigrationOperation {

     public function __construct(private Cli $cli){

     }

     public function execute(array $operation, mixed $dbdriver, mixed $snapshot) : mixed {

         $table_name = $operation['params']['name'];
         
         $this->cli->info("Attempting to delete columns from table: {$table_name}!\n");

         $colsdropped = $dbdriver->drop_columns($table_name, $operation['params']['columns']);

         if(!$colsdropped){
             $this->cli->error("Column deletion from table {$table_name} failed!\n");

             return false;
         }

         $this->cli->success("Columns dropped from table {$table_name}!\n");

         return true;
     }
    
}