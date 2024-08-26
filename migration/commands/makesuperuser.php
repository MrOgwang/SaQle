<?php
namespace SaQle\Migration\Commands;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;

class MakeSuperuser{
     
     public function __construct(private IMigrationManager $manager){

     }

     public function execute($project_root, $email, $password){
           $this->manager->make_superuser($project_root, $email, $password);
     }
}
