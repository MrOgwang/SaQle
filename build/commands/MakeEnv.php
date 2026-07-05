<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Support\Cli;

class MakeEnv {
     public static function execute(){

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
         
         //get the project folder name
         $project = basename(getcwd());

         //convert to a nice application name
         $app_name = ucwords(str_replace(['-', '_'], ' ', $project));

         //domains
         $cookie_domain = '.'.strtolower($project).'.local';
         $root_domain = 'www.'.strtolower($project).'.local';

         //database names don't usually contain hyphens
         $db_name = strtolower(str_replace('-', '_', $project));

         //read template
         $contents = file_get_contents($origin_file);

         $variables = [
             'APP_NAME'        => $app_name,
             'COOKIE_DOMAIN'   => $cookie_domain,
             'ROOT_DOMAIN'     => $root_domain,
             'DB_NAME'         => $db_name,
         ];

         foreach($variables as $key => $value){
             $contents = str_replace("{{{$key}}}", $value, $contents);
         }

         file_put_contents($destination_file, $contents);

         Cli::print("Created a development .env file\n");
     }
}
