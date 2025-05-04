<?php
namespace SaQle\Migration\Commands;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;

class ResetDatabase{
     
     public function __construct(private IMigrationManager $manager){

     }

     public function execute($project_root){
           $this->manager->reset_database($project_root);
     }
}
