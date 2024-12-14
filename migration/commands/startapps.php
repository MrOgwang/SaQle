<?php
namespace SaQle\Migration\Commands;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;

class StartApps{
     
     public function __construct(private IMigrationManager $manager){

     }

     public function execute($project_root, $name){
           $this->manager->start_apps($project_root, $name);
     }
}
