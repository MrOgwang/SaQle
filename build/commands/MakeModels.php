<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\MigrationUtils;

class MakeModels{
     private function make_models(string $project_root, $app_name = null, $db_context = null){
         $context_classes = MigrationUtils::get_context_classes($db_context);
         foreach($context_classes as $ctx){
             $models   = new $ctx()->get_permanent_models(); 
             foreach($models as $table_name => $table_schema){
                 
             }
         }
     }

     public function execute(string $project_root, $app_name = null, $db_context = null){
           $this->make_models($project_root, $app_name, $db_context);
     }
}
