<?php
namespace SaQle\Migration\Commands;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;

class MakeCollections{
     
     public function __construct(private IMigrationManager $manager){

     }

     public function execute(string $project_root, $app_name = null, $db_context = null){
         $this->manager->make_collections($project_root, $app_name, $db_context);
     }
}
