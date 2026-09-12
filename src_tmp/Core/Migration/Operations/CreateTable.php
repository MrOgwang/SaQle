<?php

namespace SaQle\Core\Migration\Operations;

use SaQle\Core\Migration\Interfaces\MigrationOperation;
use SaQle\Console\Cli;

class CreateTable implements MigrationOperation {

     public function __construct(private Cli $cli){

     }

     private function extract_field_defs(array $schema, string $table): array {
         if (!isset($schema[$table])) {
             return [];
         }

         return array_filter(array_map(fn($field) => $field['def'], $schema[$table]));
     }

     public function execute(array $operation, mixed $dbdriver, mixed $snapshot) : mixed {

         $name = $operation['params']['name'];
         
         $this->cli->info("Attempting to create table: {$name}!\n");

         $field_defs = $this->extract_field_defs($snapshot->get_model_fields(), $name);

         $unique_defs = $dbdriver->get_unique_constraint_sqls($snapshot->get_unique_constraints()[$name] ?? []);
         
         $fk_defs = $dbdriver->get_fk_constraint_sqls($snapshot->get_fk_constraints()[$name] ?? []);
         
         if(!$dbdriver->create_table_from_migration($name, $field_defs, $unique_defs, $fk_defs)){

             $this->cli->error("Table {$name} creation failed!\n");

             return false;
         }

         $this->cli->success("Table {$name} created!\n");

         return true;
     }
    
}