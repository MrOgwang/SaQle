<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\MigrationUtils;

class SeedDatabase {
     private string $seeders_folder;

     public function __construct(){
         $base_path = config('base_path');
         $this->seeders_folder = $base_path."/databases/seeders";
     }

     public function seed_database(){
         $db_seeder = config('db.seeder');
         if($db_seeder !== ''){
             $seeds = $db_seeder::get_seeds();
             foreach($seeds as $c => $seed){
                 $model = $seed['model'];
                 $file  = $this->seeders_folder."/".$seed['file'];

                 echo "Now seeding for model: {$model}\n";
                 $data = require_once $file;
                 $seeded_data = $model::create($data)->now();
                 echo "Model: {$model} seeded!\n\n";
             }
         }
     }

     public function execute(){
           $this->seed_database();
     }
}
