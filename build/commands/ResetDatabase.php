<?php
namespace SaQle\Build\Commands;

class ResetDatabase {
     
     public function reset_database(){
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
         (new SeedDatabase())->seed_database();
     }

     public function execute(){
           $this->reset_database();
     }
}
