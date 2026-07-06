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

         Cli::print("Saqle installation complete! Next steps to finish your setup >\n");
         Cli::print("1. Update email and database details in development/.env");
         Cli::print("2. Cd into the project folder and run the following commands:");
         Cli::print(">> php saqle make:migrations -n=Initial");
         Cli::print(">> php saqle migrate");
         Cli::print(">> php saqle make:superuser");
         Cli::print("\n");
         Cli::print("Enjoy SaQle. Send your feedback to wycliffomondiotieno@gmail.com\n");

         return 0;
     }  
}
