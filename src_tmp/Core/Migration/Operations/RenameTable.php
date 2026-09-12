<?php

namespace SaQle\Core\Migration\Operations;

use SaQle\Core\Migration\Interfaces\MigrationOperation;
use SaQle\Console\Cli;

class RenameTable implements MigrationOperation {

     public function __construct(private Cli $cli){

     }

     public function execute(array $operation, mixed $dbdriver, mixed $snapshot) : mixed {

         $old_name = $operation['params']['old'];
         $new_name = $operation['params']['new'];
         
         $this->cli->info("Attempting to rename table: {$old_name} to: {$new_name}!\n");

         $tblrenamed = $dbdriver->rename_table($old_name, $new_name);

         if(!$tblrenamed){
             $this->cli->error("Table {$old_name} rename failed!\n");
             
             return false;
         }

         $this->cli->success("Table {$old_name} renamed to {$new_name}!\n");
        
         return true;
     }
    
}