<?php
namespace SaQle\Build\Commands;

use Exception;
use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;

final class MakeModule extends Command {

     public function signature(): Signature {
         return Signature::make()
         ->argument(
             name: 'name',
             required: true,
             description: 'The name of the module to create'
         );
     }

     public function handle(CommandContext $context) : int {

         $name = ucfirst($context->argument('name'));

         $context->output()->info("Making module: ".$name);

         $module_dirs = [
             'Components',    //has all the module components
             'Contracts',     //has all the contracts
             'Data',          //has all the module data
             'Middleware',    //has all the module middleware
             'Routes',        //has all the module routes
             'Events',        //has all the module events
             'Listeners',     //has all the module listeners
             'Notifications', //has all notification classes
             'Contracts',     //has all request contracts
             'Models',        //has all module models
             'Validators',    //has all module validators
             'Services',      //has all the module services
             'Guards',        //has all authorization guards
             'Commands'       //has all module commands
         ];

         $module_base = path_join([config('base_path'), "src", "Modules", $name]);

         //create module directories

         foreach($module_dirs as $dir){

             $dir_path = path_join([$module_base, $dir]);

             saqle_dir()->create($dir_path);
         }

         //create module file
         $namespace = "App\\Modules\\".$name;

         self::create_module($module_base, $name, $namespace);

         $context->output()->success("Module {$name} created successfully.\n");

         return 0;
     }

     private static function create_module($module_base, $name, $namespace){
         
         $content = <<<PHP
<?php

namespace {$namespace};

use SaQle\Core\Modules\{
     Module,
     ModuleBuilder
};

class {$name} extends Module {
     
}

PHP;
         $file_path = path_join([$module_base, $name.".php"]);

         file_put_contents($file_path, $content);
     }
}
