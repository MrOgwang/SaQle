<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\MigrationUtils;
use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;

class MakeModels extends Command {

     public function signature(): Signature {
         return Signature::make();
     }

     /**
      * NOTE: This is incomplete. come back to it later
      * */
     public function handle(CommandContext $context) : int {

         $this->make_models($project_root, $app_name, $db_context);

         return 0;
     }

     private function make_models(string $project_root, $app_name = null, $db_context = null){
         $schemas = config('db.schemas');
         foreach($schemas as $schema_name => $schema_class){
             $models = new $schema_class()->get_permanent_models(); 
             foreach($models as $table_name => $model_class){
                 
             }
         }
     }

     /*public function execute(string $project_root, $app_name = null, $db_context = null){
           $this->make_models($project_root, $app_name, $db_context);
     }*/
}
