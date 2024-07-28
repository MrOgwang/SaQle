<?php
namespace SaQle\Migration\Managers;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;

class Manager implements IMigrationManager{
     public function __construct(
         private IMigrationManager $context_manager
     ){

     }

     public function get_context_snapshot(...$options){
        return $this->context_manager->get_context_snapshot(...$options);
     }
}
