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

     public function make_collections(...$options){
        return $this->context_manager->make_collections(...$options);
     }

     public function make_models(...$options){
        return $this->context_manager->make_models(...$options);
     }

     public function make_throughs(...$options){
        return $this->context_manager->make_throughs(...$options);
     }
}
