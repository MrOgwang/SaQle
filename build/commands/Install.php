<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Support\Cli;

class Install {
     public static function execute(){

         Cli::print("Installing SaQle...\n");
         
         self::copy_env();

         Cli::print("Installation complete!\n");

         return 0;
     }

     private static function copy_env(): void {

         Cli::print("Creating a development .env file\n");

         $origin_file = path_join([config('base_path'), '.env.example']);
         $destination_dir = path_join([config('base_path'), 'env', 'development']);
         $destination_file = path_join([$destination_dir, '.env']);

         if(file_exists($destination_file)){
             Cli::print(".env file already exists");
             return;
         }

         if(!file_exists($origin_file)){
             Cli::print(".env.example file not found!");
             return;
         }

         if(!is_dir($destination_dir)){
             saqle_dir()->create($destination_dir);
         }

         copy($origin_file, $destination_file);

         Cli::print("Created a development .env file\n");
     }
}
