<?php
namespace SaQle\Migration\Commands;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;

class StartProject{
     
     public function __construct(private IMigrationManager $manager){

     }

     public function execute($name){
         $this->manager->start_project(...[
             'name' => $name
         ]);
     }
}
