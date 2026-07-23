<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\MigrationUtils;
use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;
use SaQle\Core\Support\Cli;

class SeedDatabase extends Command {
     private string $seeders_folder;

     public function __construct(){
         $base_path = config('base_path');
         $this->seeders_folder = $base_path."/databases/seeders";
     }

     public function signature(): Signature {
         return Signature::make();
     }

     public function handle(CommandContext $context) : int {

         $db_seeder = config('db.seeder');

         if($db_seeder !== ''){
             $seeds = $db_seeder::get_seeds();
             foreach($seeds as $c => $seed){
                 $model = $seed['model'];
                 $file  = $this->seeders_folder."/".$seed['file'];

                 Cli::print("Now seeding for model: {$model}\n");
                 $data = require_once $file;
                 $seeded_data = $model::create($data)->now();
                 Cli::print("Model: {$model} seeded!\n\n");
             }
         }

         return 0;
     }
}
