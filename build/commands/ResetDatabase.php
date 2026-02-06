<?php
namespace SaQle\Build\Commands;

class ResetDatabase{
     
     public function reset_database($project_root){
         foreach(config('connections') as $connection_name => $params){
             $schema = config('schemas')[$connection_name];
             $schema_instance = new $schema();
             $models = $schema_instance->get_models();
             foreach($models as $table_name => $modelclass){
                 if($table_name !== 'model_temp_ids'){
                     $modelclass::empty()->now();
                 }
             }
         }
         (new SeedDatabase())->seed_database($project_root);
     }

     public function execute($project_root){
           $this->reset_database($project_root);
     }
}
