<?php

namespace SaQle\Core\Migration\Operations;

use SaQle\Core\Migration\Interfaces\MigrationOperation;
use SaQle\Console\Cli;

class AddColumns implements MigrationOperation {

     public function __construct(private Cli $cli){

     }

     public function execute(array $operation, mixed $dbdriver, mixed $snapshot) : mixed {

         $table_name = $operation['params']['name'];
         
         $this->cli->info("Attempting to add new columns table: {$table_name}!\n");

         $colsadded = $dbdriver->add_columns($table_name, $operation['params']['columns']);

         if(!$colsadded){
             $this->cli->error("Columns addition to table {$table_name} failed!\n");

             return false;
         }

         $this->cli->success("New columns added to table {$table_name}!\n");

         return true;
     }
    
}