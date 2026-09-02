<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Support\Cli;
use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;

class Install extends Command {

     public function signature(): Signature {
         return Signature::make();
     }

     public function handle(CommandContext $context) : int {
         Cli::print("Installing SaQle...\n");

         //create a logs dir
         $logs_dir = path_join([config('base_path'), 'logs']);
         if(!is_dir($logs_dir)){
             saqle_dir()->create($logs_dir);
         }

         //create a .env file
         new MakeEnv()->handle($context);

         //build the project
         new BuildProject()->handle($context);

         Cli::print("Saqle installation complete! Next steps to finish your setup >\n");
         Cli::print("1. Update email and database details in development/.env");
         Cli::print("2. Cd into the project folder and run the following commands:");
         Cli::print(">> php saqle make:migrations Initial");
         Cli::print(">> php saqle migrate");
         Cli::print(">> php saqle make:superuser");
         Cli::print("\n");
         Cli::print("Enjoy SaQle. Send your feedback to wycliffomondiotieno@gmail.com\n");

         return 0;
     }
}
