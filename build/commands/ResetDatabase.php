<?php
namespace SaQle\Build\Commands;

class ResetDatabase{
     
     public function reset_database($project_root){
         foreach(config('db_context_classes') as $classname => $params){
             $classinstance = new $classname();
             $models = $classinstance->get_models();
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
