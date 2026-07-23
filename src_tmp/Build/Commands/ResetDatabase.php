<?php
namespace SaQle\Build\Commands;

use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;

class ResetDatabase extends Command {
     
     public function signature(): Signature {
         return Signature::make();
     }

     public function handle(CommandContext $context) : int {

         foreach(config('db.connections') as $connection_name => $params){
             $schema = config('db.schemas')[$connection_name];
             $schema_instance = new $schema();
             $models = $schema_instance->get_models();
             foreach($models as $table_name => $modelclass){
                 if($table_name !== 'model_temp_ids'){
                     $modelclass::empty()->now();
                 }
             }
         }

         (new SeedDatabase())->seed_database();

         return 0;
     }
}
