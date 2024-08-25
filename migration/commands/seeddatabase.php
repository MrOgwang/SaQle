<?php
namespace SaQle\Migration\Commands;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;

class SeedDatabase{
     
     public function __construct(private IMigrationManager $manager){

     }

     public function execute($project_root){
           $this->manager->seed_database($project_root);
     }
}
