<?php

namespace SaQle\Core\Migration\Operations;

use SaQle\Core\Migration\Interfaces\MigrationOperation;
use SaQle\Core\Support\Cli;

class RenameColumn implements MigrationOperation {

     public function execute(array $operation, mixed $dbdriver, mixed $snapshot) : mixed {

         return true;
     }
    
}