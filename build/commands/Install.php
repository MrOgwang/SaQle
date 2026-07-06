<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Support\Cli;

class Install {
     public static function execute(){

         Cli::print("Installing SaQle...\n");

         //create a .env file
         MakeEnv::execute();

         //build the project
         new BuildProject()->execute();

         //run migrations
         new MakeMigrations()->execute('Initial');
         new Migrate()->execute();

         //create super user
         new MakeSuperuser()->execute();

         Cli::print("Installation complete!\n");

         return 0;
     }
}
