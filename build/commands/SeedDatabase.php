<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\MigrationUtils;

class SeedDatabase{
     public function seed_database($project_root){
         $db_seeder = config('db_seeder');
         if($db_seeder !== ''){
             $path = MigrationUtils::get_path_from_namespace($db_seeder, $project_root);
             $pathparts = explode(DIRECTORY_SEPARATOR, $path);
             array_pop($pathparts);
             $path = implode(DIRECTORY_SEPARATOR, $pathparts);
             $seeds = $db_seeder::get_seeds();
             foreach($seeds as $c => $seed){
                 $model = $seed['model'];
                 $file  = $path.DIRECTORY_SEPARATOR.$seed['file'];

                 echo "Now seeding for model: {$model}\n";
                 $data = require_once $file;
                 $seeded_data = $model::new($data)->save();
                 echo "Model: {$model} seeded!\n\n";
             }
         }
     }

     public function execute($project_root){
           $this->seed_database($project_root);
     }
}
