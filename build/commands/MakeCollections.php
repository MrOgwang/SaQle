<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\MigrationUtils;

class MakeCollections{
     private function make_collections(string $project_root, $app_name = null, $db_context = null){
         $schemas = config('schemas');
         foreach($schemas as $schema_name => $schema_class){
             $models = new $schema_class()->get_permanent_models(); 
             foreach($models as $table_name => $model_class){
                 
             }
         }
     }

     public function execute(string $project_root, $app_name = null, $db_context = null){
         $this->make_collections($project_root, $app_name, $db_context);
     }
}
