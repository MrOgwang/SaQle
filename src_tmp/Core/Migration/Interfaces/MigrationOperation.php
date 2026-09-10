<?php

namespace SaQle\Core\Migration\Interfaces;

interface MigrationOperation {
     public function execute(array $operation, mixed $dbdriver, mixed $snapshot) : mixed;
}